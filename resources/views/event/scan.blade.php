@extends('layouts.app')

@section('title', 'Scan QR-Code Event- ' . $event->nama_event)

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
    border-color: #f59e0b;
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
    background: linear-gradient(90deg, transparent, #f59e0b, transparent);
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
    background: #16a34a;
    color: white;
}
</style>
@endpush


@section('content')
<div class="event-wrap" style="padding-bottom: calc(var(--footer-h) + 80px);">

    {{-- HEADER --}}
    <div class="page-strip page-strip-event">
        <div class="live-badge">
            <span class="live-dot"></span> Scan Absen
        </div>

        <h2>
            <i class="fas fa-qrcode"></i>
            {{ Str::limit($event->nama_event, 30) }}
        </h2>

        <p>
            Jenis:
            {{ $jenis === 'masuk' ? 'Absen Masuk' : 'Absen Pulang' }}
        </p>
    </div>





    @if (session('success'))

        <a href="{{ route('event.scan', ['event'=>$event,'jenis'=>$jenis]) }}"
           class="btn-sub mb-3">
           <i class="fas fa-redo"></i> Scan Ulang
        </a>

    @else

        {{-- SCANNER --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon">
                    <i class="fas fa-camera"></i>
                </div>
                <h3>Scanner QR-Code Event</h3>
                <span class="hbadge" id="scanStatusBadge">Siap</span>
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
                        <div class="ci ci-e">
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

                </div>
            </div>
        </div>


        {{-- LOKASI --}}
        @if ($event->hasLocation())
        <div class="card">
            <div class="c-body">
                <i class="fas fa-map-marker-alt text-info"></i>
                Radius {{ $event->radius_meter }} meter
            </div>
        </div>
        @endif


        {{-- WARNING --}}
        @if (!$event->isActive())
        <div class="alert a-warn">
            Event tidak aktif
        </div>
        @endif

    @endif

</div>


{{-- ACTION --}}
<div class="action-bar">
    <a href="{{ route('event.show',$event) }}" class="ab-btn ab-btn-back">
        ← Kembali
    </a>

    @if(session('success'))
    <a href="{{ route('event.scan',['event'=>$event,'jenis'=>$jenis]) }}"
       class="ab-btn ab-btn-scan">
        Scan Lagi
    </a>
    @endif
</div>
@endsection



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

    // Show SweetAlert for session messages
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            confirmButtonColor: '#d33'
        });
    @endif

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#28a745'
        });
    @endif

    const onScanSuccess = (decodedText) => {
        if (!scanning) return;

        scanning = false;
        setStatus('Memproses...');

        // Show loading spinner
        Swal.fire({
            title: 'Memproses Absensi...',
            html: '<div class="d-flex align-items-center justify-content-center"><div class="spinner-border text-primary me-2" role="status"></div>Mohon tunggu...</div>',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        });

        fetch(`/event/{{ $event->id }}/scan`, {
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
        .then(r => {
            if (!r.ok) {
                throw new Error('Network response was not ok');
            }
            return r.json();
        })
        .then(res => {
            Swal.close(); // Close loading spinner

            if (res.errors || !res.success) {
                // Show error
                let errorMessage = 'Terjadi kesalahan saat memproses absensi.';
                if (res.errors && res.errors.barcode) {
                    errorMessage = res.errors.barcode[0];
                } else if (res.message) {
                    errorMessage = res.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: errorMessage,
                    confirmButtonColor: '#d33'
                });

                scanning = true;
                setStatus('Gagal, ulangi scan');
            } else {
                // Success - redirect or show success
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Absensi berhasil dicatat!',
                    confirmButtonColor: '#28a745',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = '{{ route('event.rekap', $event) }}';
                });
            }
        })
        .catch(error => {
            Swal.close(); // Close loading spinner

            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error Koneksi',
                text: 'Terjadi kesalahan koneksi. Silakan coba lagi.',
                confirmButtonColor: '#d33'
            });

            scanning = true;
            setStatus('Error koneksi');
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