<?php

namespace App\Console\Commands;

use App\Services\AttendanceSyncService;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class AutoFillAttendance extends Command
{
    protected $signature = 'attendance:auto-fill {--date= : Tanggal target format YYYY-MM-DD}';

    protected $description = 'Auto-create absensi masuk untuk siswa yang belum absen. Status alfa, atau izin/sakit jika ada izin disetujui.';

    public function handle(AttendanceSyncService $attendanceSync): int
    {
        $tanggal = $this->resolveTanggalOption();
        $result = $attendanceSync->autoFillMissingMasuk($tanggal);

        $this->info(sprintf(
            'Auto-fill absen masuk selesai untuk %s | siswa=%d created=%d skipped_existing=%d skipped_no_kelas=%d',
            $result['tanggal'],
            $result['total_siswa'],
            $result['created'],
            $result['skipped_existing'],
            $result['skipped_without_kelas']
        ));

        $this->line(sprintf(
            'Rincian status: hadir=%d sakit=%d izin=%d alfa=%d | poin_alfa_diberikan=%d',
            $result['created_by_status']['hadir'],
            $result['created_by_status']['sakit'],
            $result['created_by_status']['izin'],
            $result['created_by_status']['alfa'],
            $result['poin_alfa_diberikan'] ?? 0
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
