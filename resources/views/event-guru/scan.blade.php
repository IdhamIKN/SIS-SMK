@extends('layouts.app')

@section('title', 'Scan Absen Guru - ' . $eventGuru->nama_event)

@push('styles')
    @include('components.event-styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            border: 2px solid rgba(255, 255, 255, .6);
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
            border-color: #818cf8;
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
            background: linear-gradient(90deg, transparent, #818cf8, transparent);
            animation: scanMove 2s ease-in-out infinite;
        }

        @keyframes scanMove {
            0% {
                top: 10%
            }

            50% {
                top: 85%
            }

            100% {
                top: 10%
            }
        }

        .scan-status-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, .65);
            padding: 8px;
            font-size: .8rem;
            color: #fff;
            text-align: center;
        }
    </style>
@endpush

@section('content')
    @php $gtk = auth()->user()->gtk; @endphp

    <div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom:calc(var(--footer-h) + 80px);">

        <div class="page-strip" style="background:linear-gradient(135deg,#1e40af 0%,#4338ca 100%);">
            <div class="live-badge"><span class="live-dot"></span> Scan Absen Guru</div>
            <h2><i class="fas fa-qrcode"></i> {{ Str::limit($eventGuru->nama_event, 28) }}</h2>
            <p>Jenis: {{ $jenis === 'masuk' ? 'Absen Masuk' : 'Absen Pulang' }}</p>
        </div>

        @if ($gtk)
            <div class="card">
                <div class="c-body" style="padding:12px 16px;display:flex;align-items:center;gap:12px;">
                    <div
                        style="width:40px;height:40px;border-radius:10px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-user-tie" style="color:#4338ca;"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:.88rem;">{{ $gtk->nama_lengkap }}</div>
                        <div style="font-size:.73rem;color:var(--text-muted);">
                            {{ $gtk->kd_guru }}
                            @if ($gtk->nip)
                                · NIP: {{ $gtk->nip }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (!$eventGuru->isActive())
            <div class="alert a-warn"><i class="fas fa-exclamation-triangle"></i> Event tidak aktif atau sudah berakhir.
            </div>
        @else
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#e0e7ff;"><i class="fas fa-camera" style="color:#4338ca;"></i>
                    </div>
                    <h3>Scanner QR-Code</h3>
                    <span class="hbadge" id="scanStatusBadge" style="background:#e0e7ff;color:#4338ca;">Siap</span>
                </div>
                <div class="scanner-outer">
                    <div id="scanner-video"></div>
                    <div class="scan-overlay">
                        <div class="scan-frame">
                            <div class="scan-line"></div>
                        </div>
                    </div>
                    <div class="scan-status-bar" id="scanStatusText">Arahkan kamera ke QR-Code Event...</div>
                </div>
            </div>

            <div class="card">
                <div class="c-body">
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <div class="s-chip">
                            <div class="ci" style="background:#e0e7ff;color:#4338ca;"><i class="fas fa-qrcode"></i>
                            </div>
                            <div>
                                <div class="c-lbl">Jenis</div>
                                <div class="c-val">{{ $jenis === 'masuk' ? 'Masuk' : 'Pulang' }}</div>
                            </div>
                        </div>
                        <div class="s-chip">
                            <div class="ci {{ $eventGuru->isActive() ? 'ci-g' : '' }}">
                                <i class="fas fa-{{ $eventGuru->isActive() ? 'check' : 'times' }}"></i>
                            </div>
                            <div>
                                <div class="c-lbl">Event</div>
                                <div class="c-val">{{ $eventGuru->isActive() ? 'Aktif' : 'Selesai' }}</div>
                            </div>
                        </div>
                        <div class="s-chip">
                            <div class="ci" style="background:#fef3c7;color:#b45309;"><i class="fas fa-clock"></i></div>
                            <div>
                                <div class="c-lbl">Waktu</div>
                                <div class="c-val">{{ $eventGuru->tanggal_mulai->format('H:i') }} –
                                    {{ $eventGuru->tanggal_selesai->format('H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($eventGuru->hasLocation())
                <div class="card">
                    <div class="c-body"><i class="fas fa-map-marker-alt" style="color:#0ea5e9;"></i> Wajib dalam radius
                        {{ $eventGuru->radius_meter }} meter</div>
                </div>
            @endif
        @endif

    </div>

    <div class="action-bar">
        <a href="{{ route('event-guru.show', $eventGuru) }}" class="ab-btn ab-btn-back">← Kembali</a>
        <a href="{{ route('event-guru.rekap', $eventGuru) }}" class="ab-btn"
            style="background:#f0fdf4;color:#15803d;border:1px solid #86efac;">
            <i class="fas fa-table"></i> Rekap
        </a>
    </div>
@endsection

@if ($eventGuru->isActive())
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://unpkg.com/html5-qrcode"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                let scanning = true,
                    lat = null,
                    lng = null;
                const PROCESS_SCAN_URL = @json(route('event-guru.processScan', $eventGuru));
                const CSRF_TOKEN = @json(csrf_token());
                const setStatus = t => document.getElementById('scanStatusText').innerText = t;
                const setBadge = (t, c) => {
                    const el = document.getElementById('scanStatusBadge');
                    el.innerText = t;
                    el.style.background = c === 'green' ? '#dcfce7' : c === 'red' ? '#fee2e2' : '#e0e7ff';
                    el.style.color = c === 'green' ? '#15803d' : c === 'red' ? '#b91c1c' : '#4338ca';
                };
                const parseScanResponse = response => {
                    const contentType = response.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        return response.json();
                    }
                    return response.text().then(() => {
                        throw new Error('Server tidak mengembalikan respons JSON.');
                    });
                };
                @if ($eventGuru->hasLocation())
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(pos => {
                            lat = pos.coords.latitude;
                            lng = pos.coords.longitude;
                        }, () => {});
                    }
                @endif
                const onScanSuccess = decodedText => {
                    if (!scanning) return;
                    scanning = false;
                    setStatus('Memproses...');
                    setBadge('Memproses', 'yellow');
                    Swal.fire({
                        title: 'Memproses Absensi...',
                        html: '<div style="display:flex;align-items:center;justify-content:center;gap:10px;"><div style="width:20px;height:20px;border:3px solid #e2e8f0;border-top-color:#4338ca;border-radius:50%;animation:spin 0.8s linear infinite;"></div><span>Mohon tunggu...</span></div>',
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                    fetch(PROCESS_SCAN_URL, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': CSRF_TOKEN
                        },
                        body: JSON.stringify({
                            barcode: decodedText,
                            jenis: '{{ $jenis }}',
                            lat,
                            lng
                        })
                    }).then(parseScanResponse).then(res => {
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
                                    text: res.message || 'Absensi berhasil!',
                                    confirmButtonColor: '#4338ca',
                                    timer: 15000,
                                    timerProgressBar: true
                                })
                                .then(() => {
                                    if (res.redirect) window.location.href = res.redirect;
                                });
                            setBadge('Berhasil', 'green');
                            setStatus('Absen berhasil!');
                        }
                    }).catch(() => {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Koneksi',
                            text: 'Request scan tidak berhasil diproses server. Silakan muat ulang halaman dan coba lagi.',
                            confirmButtonColor: '#d33'
                        });
                        setBadge('Error', 'red');
                        setStatus('Error koneksi');
                        scanning = true;
                    });
                };
                new Html5Qrcode("scanner-video").start({
                    facingMode: "environment"
                }, {
                    fps: 10,
                    qrbox: 220
                }, onScanSuccess);
            });
        </script>
    @endpush
@endif

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header-auto-show');
            if (header) {
                header.classList.add('header-active');
            }
        });
    </script>
@endpush
