<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class EventStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_event' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'lokasi' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'radius_meter' => 'nullable|integer|min:10|max:5000',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'barcode_rotate_detik' => 'nullable|integer|min:0|max:3600',
            'mode_peserta' => 'required|in:kelas,siswa',
            'kelas_id' => 'nullable|array',
            'kelas_id.*' => 'exists:kelas,id',
            'siswa_id' => 'nullable|array',
            'siswa_id.*' => 'exists:siswas,id',
            'berlaku_untuk_semua' => 'boolean',
            'ada_absen_masuk' => 'boolean',
            'ada_absen_pulang' => 'boolean',
            'event_category_name' => 'nullable|string|max:100',
            'recurrence_type' => 'nullable|in:none,daily,weekly,monthly,yearly,custom',
            'recurrence_interval' => 'nullable|integer|min:1|max:365',
            'recurrence_days' => 'nullable|array',
            'recurrence_days.*' => 'integer|between:1,7',
            'recurrence_until' => 'nullable|date|after_or_equal:tanggal_mulai',
            'recurrence_count' => 'nullable|integer|min:2|max:366',
            'auto_point_pelanggaran' => 'boolean',
            'pasal_pelanggaran_id' => 'nullable|string|max:5',
            'poin_pelanggaran_event' => 'nullable|integer|min:1|max:9999',
            'auto_penghargaan' => 'boolean',
            'pasal_penghargaan_id' => 'nullable|string|max:5',
            'poin_penghargaan_event' => 'nullable|integer|min:1|max:9999',
            // Ekstrakurikuler
            'is_ekstrakurikuler' => 'boolean',
            'pelatih_1' => 'nullable|string|max:100',
            'pelatih_2' => 'nullable|string|max:100',
            'pelatih_3' => 'nullable|string|max:100',
            'pembina_nama' => 'nullable|string|max:150',
            'pembina_nip' => 'nullable|string|max:30',
            // Foto kegiatan
            'foto_kegiatan' => 'nullable|array|max:10',
            'foto_kegiatan.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('recurrence_type', 'none');

            if ($type === null || $type === '' || $type === 'none') {
                // cek auto_point_pelanggaran meski recurrence none
            } else {
                if (! $this->filled('recurrence_until') && ! $this->filled('recurrence_count')) {
                    $validator->errors()->add(
                        'recurrence_until',
                        'Event berulang wajib memiliki batas sampai tanggal atau jumlah kemunculan.'
                    );
                }

                if ($type === 'weekly' && empty($this->input('recurrence_days', []))) {
                    $validator->errors()->add('recurrence_days', 'Pilih minimal satu hari untuk event mingguan.');
                }
            }

            // Validasi: jika auto_point_pelanggaran aktif, pasal wajib dipilih
            if ($this->boolean('auto_point_pelanggaran') && ! $this->filled('pasal_pelanggaran_id')) {
                $validator->errors()->add(
                    'pasal_pelanggaran_id',
                    'Pasal pelanggaran wajib dipilih jika Auto Point Pelanggaran diaktifkan.'
                );
            }

            // Validasi poin_pelanggaran_event harus dalam range pasal jika pasal punya range
            if ($this->boolean('auto_point_pelanggaran') && $this->filled('pasal_pelanggaran_id') && $this->filled('poin_pelanggaran_event')) {
                $pasal = \App\Models\SubPasal::find($this->input('pasal_pelanggaran_id'));
                if ($pasal && $pasal->skormin !== $pasal->skormax) {
                    $poin = (int) $this->input('poin_pelanggaran_event');
                    if ($poin < $pasal->skormin || $poin > $pasal->skormax) {
                        $validator->errors()->add(
                            'poin_pelanggaran_event',
                            "Poin pelanggaran harus antara {$pasal->skormin} dan {$pasal->skormax}."
                        );
                    }
                }
            }

            // Validasi: jika auto_penghargaan aktif, pasal wajib dipilih
            if ($this->boolean('auto_penghargaan') && ! $this->filled('pasal_penghargaan_id')) {
                $validator->errors()->add(
                    'pasal_penghargaan_id',
                    'Pasal penghargaan wajib dipilih jika Auto Penghargaan diaktifkan.'
                );
            }

            // Validasi poin_penghargaan_event harus dalam range pasal jika pasal punya range
            if ($this->boolean('auto_penghargaan') && $this->filled('pasal_penghargaan_id') && $this->filled('poin_penghargaan_event')) {
                $pasal = \App\Models\SubPasal::find($this->input('pasal_penghargaan_id'));
                if ($pasal && $pasal->skormin !== $pasal->skormax) {
                    $poin = (int) $this->input('poin_penghargaan_event');
                    if ($poin < $pasal->skormin || $poin > $pasal->skormax) {
                        $validator->errors()->add(
                            'poin_penghargaan_event',
                            "Poin penghargaan harus antara {$pasal->skormin} dan {$pasal->skormax}."
                        );
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'nama_event.required' => 'Nama event wajib diisi.',
            'nama_event.max' => 'Nama event maksimal 255 karakter.',
            'lokasi.max' => 'Lokasi maksimal 255 karakter.',
            'lat.between' => 'Latitude harus antara -90 hingga 90.',
            'lat.numeric' => 'Latitude harus berupa angka.',
            'lng.between' => 'Longitude harus antara -180 hingga 180.',
            'lng.numeric' => 'Longitude harus berupa angka.',
            'radius_meter.integer' => 'Radius harus berupa bilangan bulat.',
            'radius_meter.min' => 'Radius minimal 10 meter.',
            'radius_meter.max' => 'Radius maksimal 5000 meter.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'barcode_rotate_detik.integer' => 'Interval rotasi barcode harus berupa bilangan bulat.',
            'barcode_rotate_detik.min' => 'Interval rotasi barcode minimal 0 detik.',
            'barcode_rotate_detik.max' => 'Interval rotasi barcode maksimal 3600 detik.',
            'mode_peserta.required' => 'Mode peserta wajib dipilih.',
            'mode_peserta.in' => 'Mode peserta harus kelas atau siswa.',
            'kelas_id.array' => 'Kelas harus berupa array.',
            'kelas_id.*.exists' => 'Kelas yang dipilih tidak ditemukan.',
            'siswa_id.array' => 'Siswa harus berupa array.',
            'siswa_id.*.exists' => 'Siswa yang dipilih tidak ditemukan.',
            'event_category_name.max' => 'Kategori event maksimal 100 karakter.',
            'recurrence_type.in' => 'Pola pengulangan tidak valid.',
            'recurrence_interval.integer' => 'Interval pengulangan harus berupa angka.',
            'recurrence_interval.min' => 'Interval pengulangan minimal 1.',
            'recurrence_interval.max' => 'Interval pengulangan maksimal 365.',
            'recurrence_days.array' => 'Hari pengulangan harus berupa daftar.',
            'recurrence_days.*.between' => 'Hari pengulangan tidak valid.',
            'recurrence_until.date' => 'Tanggal akhir pengulangan tidak valid.',
            'recurrence_until.after_or_equal' => 'Tanggal akhir pengulangan harus sama atau setelah tanggal mulai.',
            'recurrence_count.integer' => 'Jumlah kemunculan harus berupa angka.',
            'recurrence_count.min' => 'Jumlah kemunculan minimal 2.',
            'recurrence_count.max' => 'Jumlah kemunculan maksimal 366.',
            'pasal_pelanggaran_id.required_if' => 'Pasal pelanggaran wajib dipilih jika Auto Point Pelanggaran diaktifkan.',
            'pasal_penghargaan_id.required_if' => 'Pasal penghargaan wajib dipilih jika Auto Penghargaan diaktifkan.',
        ];
    }
}
