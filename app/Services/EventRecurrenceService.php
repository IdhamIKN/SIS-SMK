<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventDeletedOccurrence;
use App\Models\EventRecurrenceRule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EventRecurrenceService
{
    private const MAX_OCCURRENCES = 366;

    /**
     * Field-field non-jadwal yang ikut di-propagate ke semua child events
     * (termasuk yang sudah punya absen) saat master di-update.
     */
    private const PROPAGATE_FIELDS = [
        'nama_event',
        'deskripsi',
        'lokasi',
        'lat',
        'lng',
        'radius_meter',
        'ada_absen_masuk',
        'ada_absen_pulang',
        'berlaku_untuk_semua',
        'mode_peserta',
        'barcode_rotate_detik',
        'event_category_id',
        // Auto pelanggaran
        'auto_point_pelanggaran',
        'pasal_pelanggaran_id',
        'poin_pelanggaran_event',
        // Auto penghargaan (tambahan Agustus 2026)
        'auto_penghargaan',
        'pasal_penghargaan_id',
        'poin_penghargaan_event',
        // Ekstrakurikuler (tambahan Agustus 2026)
        'is_ekstrakurikuler',
        'pelatih_1',
        'pelatih_2',
        'pelatih_3',
        'pembina_nama',
        'pembina_nip',
    ];

    public function sync(Event $event, array $data): int
    {
        if ($event->recurrence_parent_id) {
            return 0;
        }

        $frequency = $data['recurrence_type'] ?? 'none';

        if ($frequency === 'none' || $frequency === null || $frequency === '') {
            $this->clearRule($event);

            return 0;
        }

        $rule = EventRecurrenceRule::updateOrCreate(
            ['event_id' => $event->id],
            [
                'frequency' => $frequency,
                'interval' => max(1, (int) ($data['recurrence_interval'] ?? 1)),
                'days_of_week' => $frequency === 'weekly'
                    ? collect($data['recurrence_days'] ?? [])->map(fn ($day) => (int) $day)->unique()->sort()->values()->all()
                    : null,
                'repeat_until' => $data['recurrence_until'] ?? null,
                'occurrence_count' => $data['recurrence_count'] ?? null,
            ]
        );

        $event->forceFill([
            'recurrence_rule_id' => $rule->id,
            'recurrence_parent_id' => null,
            'recurrence_sequence' => 1,
        ])->save();

        $this->deleteRegeneratableChildren($event);

        // Propagate perubahan field non-jadwal ke children yang sudah ada absen
        // (children tanpa absen sudah dihapus dan akan di-generate ulang di bawah)
        $this->propagateToExistingChildren($event);

        return $this->generateOccurrences($event->refresh(), $rule->refresh());
    }

    /**
     * Propagate perubahan field non-jadwal dari master ke semua child event
     * yang masih ada (termasuk yang sudah punya absen sehingga tidak dihapus).
     */
    private function propagateToExistingChildren(Event $root): void
    {
        $children = Event::where('recurrence_parent_id', $root->id)->get();

        if ($children->isEmpty()) {
            return;
        }

        $payload = collect(self::PROPAGATE_FIELDS)
            ->mapWithKeys(fn ($field) => [$field => $root->getAttribute($field)])
            ->all();

        foreach ($children as $child) {
            $child->update($payload);

            // Sync peserta jika berlaku_untuk_semua berubah
            if (! $root->berlaku_untuk_semua) {
                if ($root->mode_peserta === 'kelas') {
                    $kelasIds = $root->kelas()->pluck('kelas.id')->all();
                    $child->kelas()->sync($kelasIds);
                    $child->siswa()->detach();
                } else {
                    $siswaIds = $root->siswa()->pluck('siswas.id')->all();
                    $child->siswa()->sync($siswaIds);
                    $child->kelas()->detach();
                }
            } else {
                $child->kelas()->detach();
                $child->siswa()->detach();
            }
        }
    }

    private function clearRule(Event $event): void
    {
        $this->deleteRegeneratableChildren($event);

        EventRecurrenceRule::where('event_id', $event->id)->delete();

        $event->forceFill([
            'recurrence_rule_id' => null,
            'recurrence_sequence' => 1,
        ])->save();
    }

    private function deleteRegeneratableChildren(Event $event): void
    {
        // Hapus child events yang tidak punya absen (boleh di-regenerate).
        // Model::deleting hook akan otomatis menulis tombstone untuk setiap yang dihapus.
        Event::where('recurrence_parent_id', $event->id)
            ->whereDoesntHave('absenEvent')
            ->get()
            ->each(fn (Event $child) => $child->delete());
    }

    private function generateOccurrences(Event $root, EventRecurrenceRule $rule): int
    {
        $starts = $this->occurrenceStarts($root->tanggal_mulai->copy(), $rule);
        $durationSeconds = (int) $root->tanggal_mulai->diffInSeconds($root->tanggal_selesai, true);
        $existingDates = $this->existingOccurrenceDates($root);
        $kelasIds = $root->kelas()->pluck('kelas.id')->all();
        $siswaIds = $root->siswa()->pluck('siswas.id')->all();
        $created = 0;
        $sequence = 2;

        foreach ($starts as $start) {
            $dateKey = $start->format('Y-m-d');

            if ($existingDates->has($dateKey)) {
                $sequence++;
                continue;
            }

            // Skip past dates that are no longer in the DB.
            // If a child date is in the past and doesn't exist, it was intentionally
            // deleted — do NOT resurrect it and risk spurious auto-point processing.
            if ($start->lt(now()->startOfDay()) && ! $existingDates->has($dateKey)) {
                $sequence++;
                continue;
            }

            $child = $this->createOccurrence($root, $rule, $start, $durationSeconds, $sequence);

            if (! $child->berlaku_untuk_semua) {
                if ($child->mode_peserta === 'kelas') {
                    $child->kelas()->sync($kelasIds);
                } else {
                    $child->siswa()->sync($siswaIds);
                }
            }

            $existingDates->put($dateKey, true);
            $created++;
            $sequence++;
        }

        return $created;
    }

    private function existingOccurrenceDates(Event $root): Collection
    {
        // Tanggal yang masih ada di DB
        $dates = Event::where('recurrence_parent_id', $root->id)
            ->get(['tanggal_mulai'])
            ->mapWithKeys(fn (Event $event) => [$event->tanggal_mulai->format('Y-m-d') => true]);

        // Tanggal root sendiri
        $dates->put($root->tanggal_mulai->format('Y-m-d'), true);

        // Tombstone: tanggal yang pernah ada dan sudah dihapus user — JANGAN dibuat ulang
        $tombstones = EventDeletedOccurrence::where('master_event_id', $root->id)
            ->pluck('occurrence_date')
            ->each(fn ($date) => $dates->put(
                $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : (string) $date,
                true
            ));

        return $dates;
    }

    private function createOccurrence(
        Event $root,
        EventRecurrenceRule $rule,
        Carbon $start,
        int $durationSeconds,
        int $sequence
    ): Event {
        return Event::create([
            'created_by'             => $root->created_by,
            'event_category_id'      => $root->event_category_id,
            'nama_event'             => $root->nama_event,
            'deskripsi'              => $root->deskripsi,
            'tanggal_mulai'          => $start,
            'tanggal_selesai'        => $start->copy()->addSeconds($durationSeconds),
            'lokasi'                 => $root->lokasi,
            'lat'                    => $root->lat,
            'lng'                    => $root->lng,
            'radius_meter'           => $root->radius_meter,
            'ada_absen_masuk'        => $root->ada_absen_masuk,
            'ada_absen_pulang'       => $root->ada_absen_pulang,
            'berlaku_untuk_semua'    => $root->berlaku_untuk_semua,
            'mode_peserta'           => $root->mode_peserta,
            'barcode_rotate_detik'   => $root->barcode_rotate_detik,
            'barcode_value'          => hash('sha256', $root->id.$sequence.microtime().random_bytes(16)),
            'barcode_updated_at'     => now(),
            // Auto pelanggaran
            'auto_point_pelanggaran' => $root->auto_point_pelanggaran,
            'pasal_pelanggaran_id'   => $root->pasal_pelanggaran_id,
            'poin_pelanggaran_event' => $root->poin_pelanggaran_event,
            // Auto penghargaan
            'auto_penghargaan'       => $root->auto_penghargaan,
            'pasal_penghargaan_id'   => $root->pasal_penghargaan_id,
            'poin_penghargaan_event' => $root->poin_penghargaan_event,
            // Ekstrakurikuler
            'is_ekstrakurikuler'     => $root->is_ekstrakurikuler,
            'pelatih_1'              => $root->pelatih_1,
            'pelatih_2'              => $root->pelatih_2,
            'pelatih_3'              => $root->pelatih_3,
            'pembina_nama'           => $root->pembina_nama,
            'pembina_nip'            => $root->pembina_nip,
            // Recurrence
            'recurrence_parent_id'   => $root->id,
            'recurrence_rule_id'     => $rule->id,
            'recurrence_sequence'    => $sequence,
        ]);
    }

    /**
     * Returns generated starts after the root occurrence. Count includes the root.
     */
    private function occurrenceStarts(Carbon $rootStart, EventRecurrenceRule $rule): array
    {
        $limit = $rule->occurrence_count
            ? max(0, min(self::MAX_OCCURRENCES, $rule->occurrence_count) - 1)
            : self::MAX_OCCURRENCES - 1;
        $until = $rule->repeat_until?->copy()->endOfDay();
        $interval = max(1, (int) $rule->interval);

        return match ($rule->frequency) {
            'weekly' => $this->weeklyStarts($rootStart, $rule, $interval, $limit, $until),
            'monthly' => $this->fixedPeriodStarts($rootStart, 'month', $interval, $limit, $until),
            'yearly' => $this->fixedPeriodStarts($rootStart, 'year', $interval, $limit, $until),
            'custom' => $this->fixedPeriodStarts($rootStart, 'day', $interval, $limit, $until),
            default => $this->fixedPeriodStarts($rootStart, 'day', $interval, $limit, $until),
        };
    }

    private function fixedPeriodStarts(Carbon $rootStart, string $unit, int $interval, int $limit, ?Carbon $until): array
    {
        $starts = [];
        $cursor = $rootStart->copy();

        while (count($starts) < $limit) {
            match ($unit) {
                'month' => $cursor->addMonthsNoOverflow($interval),
                'year' => $cursor->addYearsNoOverflow($interval),
                default => $cursor->addDays($interval),
            };

            if (! $this->withinEnd($cursor, $until)) {
                break;
            }

            $starts[] = $cursor->copy();
        }

        return $starts;
    }

    private function weeklyStarts(
        Carbon $rootStart,
        EventRecurrenceRule $rule,
        int $interval,
        int $limit,
        ?Carbon $until
    ): array {
        $days = collect($rule->days_of_week ?: [$rootStart->isoWeekday()])
            ->map(fn ($day) => (int) $day)
            ->filter(fn ($day) => $day >= 1 && $day <= 7)
            ->unique()
            ->values();
        $starts = [];
        $cursor = $rootStart->copy()->addDay()->startOfDay();
        $anchorWeek = $rootStart->copy()->startOfWeek(Carbon::MONDAY);
        $safetyDays = 0;

        while (count($starts) < $limit && $safetyDays < (self::MAX_OCCURRENCES * 14)) {
            $candidate = $cursor->copy()->setTime(
                (int) $rootStart->format('H'),
                (int) $rootStart->format('i'),
                (int) $rootStart->format('s')
            );
            $weekDistance = $anchorWeek->diffInWeeks($candidate->copy()->startOfWeek(Carbon::MONDAY));

            if (
                $days->contains($candidate->isoWeekday())
                && $weekDistance % $interval === 0
                && $candidate->gt($rootStart)
            ) {
                if (! $this->withinEnd($candidate, $until)) {
                    break;
                }

                $starts[] = $candidate;
            }

            $cursor->addDay();
            $safetyDays++;
        }

        return $starts;
    }

    private function withinEnd(Carbon $start, ?Carbon $until): bool
    {
        return $until === null || $start->lte($until);
    }
}
