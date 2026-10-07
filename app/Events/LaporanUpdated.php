<?php

namespace App\Events;

use App\Models\LaporanKehadiranGuru;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LaporanUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $laporan;

    /**
     * Create a new event instance.
     */
    public function __construct(LaporanKehadiranGuru $laporan)
    {
        $this->laporan = $laporan;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('panel.realtime'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'laporan.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'kelas_id' => $this->laporan->kelas_id,
            'gtk_nama' => $this->laporan->gtk->nama_lengkap,
            'gtk_id' => $this->laporan->gtk_id,
            'jam_ke' => $this->laporan->jam_ke,
            'status' => $this->laporan->status,
            'status_label' => $this->laporan->status_label,
            'waktu_laporan' => $this->laporan->waktu_laporan ? $this->laporan->waktu_laporan->format('H:i') : null,
            'jadwal_kbm_id' => $this->laporan->jadwal_kbm_id,
            'tanggal' => $this->laporan->tanggal->format('Y-m-d'),
        ];
    }
}