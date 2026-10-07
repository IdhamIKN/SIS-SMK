@extends('layouts.app')

@section('title', 'Scan Absen Guru - ' . $event->nama_event)

@push('styles')
@include('components.event-styles')

<style>
.scanner-outer {
    position: relative;
    width: 100%;
    background: #000;
    overflow: hidden;
}

#scanner-video {
    width: 100%;
    min-height: 280px;
    display: block;
    object-fit: cover;
}

.scan-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.scan-frame {
    width: 220px;
    height: 220px;
    border: 2px solid rgba(255,255,255,.6);
    border-radius: 12px;
    position: relative;
    overflow: hidden;
}

.scan-frame::before,
.scan-frame::after {
    content: '';
    position: absolute;
    width: 24px;
    height: 24px;
    border-color: #3b82f6;
    border-style: solid;
}

.scan-frame::before {
    top: -1px;
    left: -1px;
    border-width: 3px 0 0 3px;
}

.scan-frame::after {
    bottom: -1px;
    right: -1px;
    border-width: 0 3px 3px 0;
}

.scan-line {
    position: absolute;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, #3b82f6, transparent);
    animation: scanMove 2s ease-in-out infinite;
}

@keyframes scanMove {
    0% { top: 10%; }
    50% { top: 85%; }
    100% { top: 10%; }
}

.scan-status-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,.65);
    padding: 8px;
    font-size: .8rem;
    color: #fff;
    text-align: center;
}

.action-bar {
    position: fixed;
    bottom: var(--footer-h);
    left: 0;
    right: 0;
    padding: 10px 16px;
    background: rgba(255,255,255,.96);
    display: flex;
    gap: 10px;
    z-index: 999;
}

.ab-btn {
    flex: 1;
    padding: 12px;
    border-radius: 12px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
}

.ab-btn-back {
    background: #f1f5f9;
    color: #475569;
}

.ab-btn-scan {
    background: #2563eb;
    color: white;
}

.guru-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    color: #1d4ed8;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: .78rem;
    font-weight: 600;
    margin-bottom: 10px;
}
</style>
@endpush


@section('content')
<div class="event-wrap" style="padding-bottom: calc(var(--footer-h) + 80px);">

    {{-- HEADER --}}
    <div class="page-strip" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);">
        <div class="live-badge">
            <span class="live-dot"></span> Scan Absen Guru
        </div>

        <h2>
            <i class="fas fa-chalkboard-teacher"></i>
            {{ Str::limit($event->nama_event, 30) }}
        </h2>

        <p>
            Jenis: {{ $jenis === 'masuk' ? 'Absen Masuk' : 'Absen Pulang' }}
        </p>
    </div>

    {{-- INFO GURU --}}
    @php $gtk = auth()->user()->gtk; @endphp
    @if ($gtk)
        <div class="card">
            <div class="c-body" style="padding: 12px 16px;">
                <div class="guru-badge">
                    <i class="fas fa-user-tie"></i>
                    {{ $gtk->nama_lengkap }}
                </div>
                <div style="font-size: .78rem; color: var(--text-muted);">
                    <i class="fas fa-id-badge"></i> {{ $gtk->kd_guru }}
                    @if ($gtk->nip)
                        &nbsp;·&nbsp; NIP: {{ $gtk->nip }}
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- SCANNER --}}
    @if (!$event->isActive())
        <div class="alert a-warn">
            <i class="fas fa-exclamation-triangle"></i> Event tidak aktif atau sudah berakhir.
        </div>
    @else
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background: #eff6ff;">
                    <i class="fas fa-camera" style="color: #2563eb;"></i>
                </div>
                <h3>Scanner QR-Code</h3>
                <span class="hbadge" id="scanStatusBadge" style="background: #dbeafe; color: #1d4ed8;">Siap</span>
            </div>

            <div class="scanner-outer">
                <div id="scanner-video"></div>

                <div class="scan-overlay">
                    <div class="scan-frame">
                        <div class="scan-line"></div>
                    </div>
                </div>

                <div class="scan-status-bar" id="scanStatusText">
                    Arahkan kamera ke QR-Code Event...
                </div>
            </div>
        </div>

        {{-- STATUS --}}
        <div class="card">
            <div class="c-body">
                <div style="display:flex; gap:10px; flex-wrap:wrap;">

                    <div class="s-chip">
                        <div class="ci" style="background: #eff6ff; color: #2563eb;">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <div>
                            <div class="c-lbl">Jenis</div>
                            <div class="c-val">
                                {{ $jenis === 'masuk' ? 'Masuk' : 'Pulang' }}
                            </div>
                        </div>
                    </div>

                    <div class="s-chip">
                        <div class="ci {{ $event->isActive() ? 'ci-g' : '' }}">
                            <i class="fas fa-{{ $event->isActive() ? 'check' : 'times' }}"></i>
                        </div>
                        <div>
                            <div class="c-lbl">Event</div>
                            <div class="c-val">
                                {{ $event->isActive() ? 'Aktif' : 'Selesai' }}
                            </div>
                        </div>
                    </div>

                    <div class="s-chip">
                        <div class="ci" style="background: #fef3c7; color: #b45309;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="c-lbl">Waktu</div>
                            <div class="c-val">
                                {{ $event->tanggal_mulai->format('H:i') }} – {{ $event->tanggal_selesai->format('H:i') }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        @if ($event->hasLocation())
            <div class="card">
                <div class="c-body">
                    <i class="fas fa-map-marker-alt text-info"></i>
                    Wajib dalam radius {{ $event->radius_meter }} meter
                </div>
            </div>
        @endif
    @endif

</div>

{{-- ACTION --}}
<div class="action-bar">
    <a href="{{ route('event.show', $event) }}" class="ab-btn ab-btn-back">
        ← Kembali
    </a>
    <a href="{{ route('event-guru.rekap', $event) }}" class="ab-btn" style="background:#f0fdf4; color:#15803d; border:1px solid #86efac;">
        <i class="fas fa-table"></i> Rekap
    </a>
</div>
@endsection


@if ($event->isActive())
@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    let scanning = true;
    let lat = null;
    let lng = null;

    const setStatus = (text) => {
        document.getElementById('scanStatusText').innerText = text;
    };

    const setBadge = (text, color) => {
        const el = document.getElementById('scanStatusBadge');
        el.innerText = text;
        el.style.background = color === 'green' ? '#dcfce7' : color === 'red' ? '#fee2e2' : '#dbeafe';
        el.style.color = color === 'green' ? '#15803d' : color === 'red' ? '#b91c1c' : '#1d4ed8';
    };

    // Ambil geolokasi jika event punya lokasi
    @if ($event->hasLocation())
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                lat = pos.coords.latitude;
                lng = pos.coords.longitude;
            },
            () => {}
        );
    }
    @endif

    const onScanSuccess = (decodedText) => {
        if (!scanning) return;

        scanning = false;
        setStatus('Memproses...');
        setBadge('Memproses', 'yellow');

        Swal.fire({
            title: 'Memproses Absensi...',
            html: '<div class="d-flex align-items-center justify-content-center"><div class="spinner-border text-primary me-2" role="status"></div>Mohon tunggu...</div>',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        });

        fetch(`/event/{{ $event->id }}/guru/scan`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                barcode: decodedText,
                jenis: '{{ $jenis }}',
                lat: lat,
                lng: lng
            })
        })
        .then(r => r.json())
        .then(res => {
            Swal.close();

            if (!res.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: res.message || 'Terjadi kesalahan.',
                    confirmButtonColor: '#d33'
                });
                setBadge('Gagal', 'red');
                setStatus('Gagal, ulangi scan');
                scanning = true;
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Absensi berhasil dicatat!',
                    confirmButtonColor: '#2563eb',
                    timer: 2500,
                    timerProgressBar: true
                }).then(() => {
                    if (res.redirect) {
                        window.location.href = res.redirect;
                    }
                });
                setBadge('Berhasil', 'green');
                setStatus('Absen berhasil!');
            }
        })
        .catch(err => {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error Koneksi',
                text: 'Terjadi kesalahan koneksi. Silakan coba lagi.',
                confirmButtonColor: '#d33'
            });
            setBadge('Error', 'red');
            setStatus('Error koneksi');
            scanning = true;
        });
    };

    const html5QrCode = new Html5Qrcode("scanner-video");

    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: 220 },
        onScanSuccess
    );

});
</script>
@endpush
@endif
