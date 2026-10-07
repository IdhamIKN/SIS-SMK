<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolConfigUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Informasi Dasar Sekolah
            'sekolah' => 'required|string|max:255',
            'alsekolah' => 'nullable|string|max:500',
            'telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'kab' => 'nullable|string|max:100',
            'alias' => 'nullable|string|max:50',

            // Kepala Sekolah
            'nama_ks' => 'nullable|string|max:255',
            'nip_ks' => 'nullable|string|max:50',

            // Wakil Kepala Sekolah
            'nama_waka' => 'nullable|string|max:255',
            'nip_waka' => 'nullable|string|max:50',

            // Ketua
            'nama_ketua' => 'nullable|string|max:255',
            'nip_ketua' => 'nullable|string|max:50',

            // Website & Media
            'site_url' => 'nullable|url|max:255',
            'site_logo' => 'nullable|url|max:255',
            'wasekolah' => 'nullable|string|max:20',

            // Jam Sekolah
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'jam_masuk_khusus' => 'nullable|date_format:H:i',
            'jam_pulang_khusus' => 'nullable|date_format:H:i',
            'hari_efektif' => 'nullable|array|min:1',
            'hari_efektif.*' => 'string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'hari_khusus' => 'nullable|array',
            'hari_khusus.*' => 'string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',

            // Konfigurasi Keterlambatan & Batas Absen
            'jam_mulai_absensi'  => 'nullable|date_format:H:i',
            'batas_tepat_waktu'  => 'nullable|date_format:H:i',
            'batas_absen_masuk'  => 'nullable|date_format:H:i',

            // Lokasi & Sistem
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_meter' => 'nullable|integer|min:10|max:50000',
            'system_name' => 'nullable|string|max:255',

            // Notifikasi WA saat presensi masuk
            'wa_notif_masuk_enabled'  => 'nullable|boolean',
            // Notifikasi WA saat presensi pulang
            'wa_notif_pulang_enabled' => 'nullable|boolean',
            // Notifikasi WA saat absen event
            'wa_notif_event_enabled'  => 'nullable|boolean',
            // Notifikasi WA saat ambang poin tatib
            'wa_notif_tatib_enabled'  => 'nullable|boolean',
            // Notifikasi WA laporan kehadiran guru
            'wa_notif_laporan_guru_enabled' => 'nullable|boolean',
            'wa_notif_laporan_guru_nomor'   => 'nullable|string|max:1000',

            // Konfigurasi Auto Alfa
            'auto_alfa_enabled'       => 'nullable|boolean',
            'jam_eksekusi_auto_alfa'  => 'nullable|date_format:H:i',
            'auto_point_alfa_enabled' => 'nullable|boolean',
            'pasal_alfa_id'           => 'nullable|string|max:20',
            'wa_notif_alfa_enabled'   => 'nullable|boolean',

            // Konfigurasi Auto Poin Hadir & Terlambat
            'auto_poin_hadir_enabled'     => 'nullable|boolean',
            'pasal_hadir_id'              => 'nullable|string|max:20',
            'auto_poin_terlambat_enabled' => 'nullable|boolean',
            'pasal_terlambat_id'          => 'nullable|string|max:20',

            // Mode Libur Panjang
            'libur_mode'    => 'nullable|boolean',
            'libur_dari'    => 'nullable|date',
            'libur_sampai'  => 'nullable|date|after_or_equal:libur_dari',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateTimeOrder(
                $validator,
                $this->input('jam_masuk'),
                $this->input('jam_pulang'),
                'jam_pulang',
                'Jam pulang normal harus setelah jam masuk normal.'
            );

            $jamMasukKhusus = $this->input('jam_masuk_khusus') ?: $this->input('jam_masuk');
            $jamPulangKhusus = $this->input('jam_pulang_khusus') ?: $this->input('jam_pulang');
            $this->validateTimeOrder(
                $validator,
                $jamMasukKhusus,
                $jamPulangKhusus,
                'jam_pulang_khusus',
                'Jam pulang khusus harus setelah jam masuk khusus atau jam masuk normal.'
            );

            // Validasi jam_mulai_absensi < batas_tepat_waktu
            $jamMulaiAbsensi = $this->input('jam_mulai_absensi');
            $batasTepat = $this->input('batas_tepat_waktu');
            if ($jamMulaiAbsensi && $batasTepat) {
                $this->validateTimeOrder(
                    $validator,
                    $jamMulaiAbsensi,
                    $batasTepat,
                    'batas_tepat_waktu',
                    'Batas akhir masuk tepat waktu harus setelah jam mulai absensi.'
                );
            }

            // Validasi batas_tepat_waktu < batas_absen_masuk
            $batasAbsenMasuk = $this->input('batas_absen_masuk');
            if ($batasTepat && $batasAbsenMasuk) {
                $this->validateTimeOrder(
                    $validator,
                    $batasTepat,
                    $batasAbsenMasuk,
                    'batas_absen_masuk',
                    'Batas akhir absen masuk harus setelah batas tepat waktu (agar siswa terlambat masih bisa absen).'
                );
            }
        });
    }

    private function validateTimeOrder($validator, mixed $start, mixed $end, string $field, string $message): void
    {
        if (! $this->isTimeValue($start) || ! $this->isTimeValue($end)) {
            return;
        }

        if ($end <= $start) {
            $validator->errors()->add($field, $message);
        }
    }

    private function isTimeValue(mixed $value): bool
    {
        return is_string($value) && preg_match('/^\d{2}:\d{2}$/', $value) === 1;
    }

    public function messages(): array
    {
        return [
            // Informasi Dasar Sekolah
            'sekolah.required' => 'Nama sekolah wajib diisi.',
            'sekolah.max' => 'Nama sekolah maksimal 255 karakter.',
            'alsekolah.max' => 'Alamat sekolah maksimal 500 karakter.',
            'telp.max' => 'Nomor telepon maksimal 20 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'kab.max' => 'Kabupaten maksimal 100 karakter.',
            'alias.max' => 'Alias maksimal 50 karakter.',

            // Kepala Sekolah
            'nama_ks.max' => 'Nama kepala sekolah maksimal 255 karakter.',
            'nip_ks.max' => 'NIP kepala sekolah maksimal 50 karakter.',

            // Wakil Kepala Sekolah
            'nama_waka.max' => 'Nama wakil kepala sekolah maksimal 255 karakter.',
            'nip_waka.max' => 'NIP wakil kepala sekolah maksimal 50 karakter.',

            // Ketua
            'nama_ketua.max' => 'Nama ketua maksimal 255 karakter.',
            'nip_ketua.max' => 'NIP ketua maksimal 50 karakter.',

            // Website & Media
            'site_url.url' => 'URL website tidak valid.',
            'site_url.max' => 'URL website maksimal 255 karakter.',
            'site_logo.url' => 'URL logo tidak valid.',
            'site_logo.max' => 'URL logo maksimal 255 karakter.',
            'wasekolah.max' => 'Nomor WhatsApp sekolah maksimal 20 karakter.',

            // Jam Sekolah
            'jam_masuk.date_format' => 'Format jam masuk tidak valid (gunakan format HH:MM).',
            'jam_pulang.date_format' => 'Format jam pulang tidak valid (gunakan format HH:MM).',
            'jam_masuk_khusus.date_format' => 'Format jam masuk khusus tidak valid (gunakan format HH:MM).',
            'jam_pulang_khusus.date_format' => 'Format jam pulang khusus tidak valid (gunakan format HH:MM).',
            'hari_efektif.min' => 'Minimal pilih 1 hari efektif sekolah.',
            'hari_efektif.*.in' => 'Hari efektif tidak valid.',
            'hari_khusus.*.in' => 'Hari khusus tidak valid.',

            // Konfigurasi Keterlambatan
            'jam_mulai_absensi.date_format' => 'Format jam mulai absensi tidak valid (gunakan format HH:MM).',
            'batas_tepat_waktu.date_format' => 'Format batas akhir tepat waktu tidak valid (gunakan format HH:MM).',
            'batas_absen_masuk.date_format' => 'Format batas akhir absen masuk tidak valid (gunakan format HH:MM).',

            // Lokasi & Sistem
            'latitude.between' => 'Latitude harus antara -90 hingga 90.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'longitude.between' => 'Longitude harus antara -180 hingga 180.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'radius_meter.integer' => 'Radius absensi harus berupa angka bulat.',
            'radius_meter.min' => 'Radius absensi minimal 10 meter.',
            'radius_meter.max' => 'Radius absensi maksimal 50000 meter.',
            'system_name.max' => 'Nama sistem maksimal 255 karakter.',

            // Auto Alfa
            'jam_eksekusi_auto_alfa.date_format' => 'Format jam eksekusi Auto Alfa tidak valid (gunakan format HH:MM).',
            'pasal_alfa_id.max'                  => 'ID pasal alfa maksimal 20 karakter.',
        ];
    }
}
