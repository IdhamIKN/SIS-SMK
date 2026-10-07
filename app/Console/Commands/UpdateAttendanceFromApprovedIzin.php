<?php

namespace App\Console\Commands;

use App\Services\AttendanceSyncService;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class UpdateAttendanceFromApprovedIzin extends Command
{
    protected $signature = 'attendance:update-from-approved-izin {--date= : Tanggal target format YYYY-MM-DD}';

    protected $description = 'Update status absensi masuk dari pengajuan izin yang sudah disetujui pada tanggal target.';

    public function handle(AttendanceSyncService $attendanceSync): int
    {
        $tanggal = $this->resolveTanggalOption();
        $result = $attendanceSync->syncApprovedIzinForDate($tanggal);

        $this->info(sprintf(
            'Sinkron izin -> absen selesai untuk %s | izin=%d siswa=%d updated=%d created=%d unchanged=%d',
            $result['tanggal'],
            $result['izin_count'],
            $result['siswa_terpengaruh'],
            $result['updated'],
            $result['created'],
            $result['unchanged']
        ));

        $this->line(sprintf(
            'Dilewati: hadir=%d manual=%d no_kelas=%d',
            $result['skipped_hadir'],
            $result['skipped_manual'],
            $result['skipped_without_kelas']
        ));

        return self::SUCCESS;
    }

    private function resolveTanggalOption(): ?Carbon
    {
        $date = $this->option('date');

        if (! $date) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', $date, config('app.timezone', 'Asia/Jakarta'))->startOfDay();
    }
}

