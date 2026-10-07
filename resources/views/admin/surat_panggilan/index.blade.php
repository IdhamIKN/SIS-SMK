@extends('layouts.app')

@section('title', 'Surat Panggilan Orang Tua')

@push('styles')
    @include('components.event-styles')
    @include('admin.tatib._styles')
@endpush

@section('content')
    <div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">

        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>{{ now()->translatedFormat('l, d F Y') }}</div>
            <h2><i class="fas fa-envelope-open-text"></i> Surat Panggilan Orang Tua</h2>
            <p>Daftar surat panggilan yang telah dibuat</p>
        </div>

        @if (session('success'))
            <div
                style="background:#dcfce7;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:12px;color:#15803d;font-size:.85rem;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Filter & Tombol Buat --}}
        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;margin-bottom:12px;">
            <form method="GET" style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-end;flex:1;min-width:0;">
                <div class="tatib-field" style="min-width:160px;">
                    <label>Tahun Ajaran</label>
                    <select name="tahun_ajaran" class="tatib-select" onchange="this.form.submit()">
                        @foreach ($tahunList as $t)
                            <option value="{{ $t }}" @selected($tahunAjaran == $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tatib-field" style="min-width:220px;flex:1;">
                    <label>Cari Siswa</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="tatib-input"
                        placeholder="Nama atau NIS...">
                </div>
                <button type="submit" class="tatib-btn tatib-btn-primary" style="margin-bottom:0;">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>
            <a href="{{ route('admin.surat-panggilan.create', ['tahun_ajaran' => $tahunAjaran]) }}"
                class="tatib-btn tatib-btn-green" style="margin-bottom:0;">
                <i class="fas fa-plus"></i> Buat Surat
            </a>
        </div>

        {{-- Tabel --}}
        <div class="tatib-form-card" style="padding:0;overflow:hidden;">
            @if ($suratPanggilan->isEmpty())
                <div style="text-align:center;padding:40px 20px;color:#94a3b8;">
                    <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.4;"></i>
                    Belum ada surat panggilan untuk tahun ajaran ini.
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table class="tatib-table" style="border-radius:0;">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Panggilan</th>
                                <th>Tanggal Acara</th>
                                <th>Penandatangan</th>
                                <th>Dibuat</th>
                                <th style="width:120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suratPanggilan as $i => $sp)
                                <tr>
                                    <td style="color:#94a3b8;text-align:center;">
                                        {{ $suratPanggilan->firstItem() + $i }}
                                    </td>
                                    <td>
                                        <strong style="font-size:.85rem;">{{ $sp->siswa?->nama_lengkap ?? '-' }}</strong>
                                        @if ($sp->nomor_surat)
                                            <div style="font-size:.72rem;color:#94a3b8;">{{ $sp->nomor_surat }}</div>
                                        @endif
                                    </td>
                                    <td style="font-size:.82rem;">{{ $sp->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                                    <td style="text-align:center;">
                                        <span class="tatib-pill tatib-pill-amber">Ke-{{ $sp->panggilan_ke }}</span>
                                    </td>
                                    <td style="font-size:.82rem;white-space:nowrap;">
                                        {{ $sp->hari }},
                                        {{ $sp->tanggal_acara?->translatedFormat('d F Y') }}
                                    </td>
                                    <td style="font-size:.82rem;">
                                        {{ $sp->gtk?->nama_lengkap ?? '-' }}
                                        @if ($sp->gtk?->jabatan)
                                            <div style="font-size:.7rem;color:#94a3b8;">{{ $sp->gtk->jabatan }}</div>
                                        @endif
                                    </td>
                                    <td style="font-size:.75rem;color:#94a3b8;white-space:nowrap;">
                                        {{ $sp->created_at?->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                                            <a href="{{ route('admin.surat-panggilan.preview', $sp) }}"
                                                class="tatib-btn tatib-btn-blue"
                                                style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;padding:5px 9px;font-size:.72rem;"
                                                target="_blank" title="Pratinjau Surat">
                                                <i class="fas fa-print"></i> Cetak
                                            </a>
                                            {{-- Tombol Kirim WA --}}
                                            <button type="button" class="tatib-btn"
                                                style="background:{{ $sp->wa_sent_at ? '#f0fdf4' : '#fefce8' }};
                                                       color:{{ $sp->wa_sent_at ? '#15803d' : '#854d0e' }};
                                                       border:1px solid {{ $sp->wa_sent_at ? '#bbf7d0' : '#fde68a' }};
                                                       padding:5px 9px;font-size:.72rem;"
                                                title="{{ $sp->wa_sent_at
                                                    ? 'Terkirim ' . $sp->wa_sent_at->format('d/m H:i') . ' → ' . $sp->wa_nomor_tujuan . ' (klik kirim ulang)'
                                                    : 'Kirim notifikasi WhatsApp ke orang tua' }}"
                                                onclick="bukaModalWa({{ $sp->id }}, '{{ addslashes($sp->siswa?->nama_lengkap) }}', {{ $sp->siswa_id }})">
                                                <i class="fab fa-whatsapp"></i>
                                                {{ $sp->wa_sent_at ? '✓ WA' : 'WA' }}
                                            </button>
                                            <form method="POST" action="{{ route('admin.surat-panggilan.destroy', $sp) }}"
                                                onsubmit="return confirm('Hapus surat panggilan ini?')"
                                                style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="tatib-btn tatib-btn-danger"
                                                    style="padding:5px 9px;font-size:.72rem;" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($suratPanggilan->hasPages())
                    <div style="padding:12px 16px;">
                        {{ $suratPanggilan->withQueryString()->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
    {{-- Modal Kirim WA --}}
    <div id="modal-kirim-wa"
        style="display:none;position:fixed;inset:0;z-index:9999;
     background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
        <div
            style="background:#fff;border-radius:14px;padding:24px;width:min(440px,92vw);
                box-shadow:0 8px 32px rgba(0,0,0,.18);">

            {{-- Header --}}
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div
                    style="background:#dcfce7;border-radius:50%;width:40px;height:40px;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fab fa-whatsapp" style="color:#15803d;font-size:1.2rem;"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:.95rem;">Kirim Notifikasi WhatsApp</div>
                    <div id="wa-modal-nama" style="font-size:.8rem;color:#64748b;"></div>
                </div>
            </div>

            {{-- Pilihan nomor dari database --}}
            <div id="wa-pilihan-nomor" style="display:none;margin-bottom:12px;">
                <div style="font-size:.78rem;font-weight:600;color:#475569;margin-bottom:6px;">
                    <i class="fas fa-address-book"></i> Nomor tersimpan — klik untuk mengisi:
                </div>
                <div id="wa-list-nomor" style="display:flex;flex-direction:column;gap:6px;"></div>
                <div style="border-top:1px dashed #e2e8f0;margin:10px 0;"></div>
            </div>

            <form id="form-kirim-wa" method="POST">
                @csrf
                <div class="tatib-field">
                    <label style="font-size:.82rem;font-weight:600;">
                        Nomor HP Tujuan
                        <span id="wa-loading" style="display:none;font-size:.72rem;color:#94a3b8;font-weight:400;">
                            <i class="fas fa-spinner fa-spin"></i> Memuat nomor...
                        </span>
                    </label>
                    <input type="text" name="nomor_hp" id="wa-input-nomor" class="tatib-input"
                        placeholder="08xxx atau 628xxx" inputmode="numeric"
                        style="font-size:.9rem;font-weight:600;letter-spacing:.5px;">
                    <div style="font-size:.72rem;color:#94a3b8;margin-top:5px;">
                        <i class="fas fa-link"></i>
                        Pesan disertai link PDF surat panggilan (berlaku 7 hari).
                    </div>
                </div>

                <div style="display:flex;gap:8px;margin-top:16px;justify-content:flex-end;">
                    <button type="button" onclick="tutupModalWa()" class="tatib-btn"
                        style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;">
                        Batal
                    </button>
                    <button type="submit" class="tatib-btn tatib-btn-green" style="gap:6px;">
                        <i class="fab fa-whatsapp"></i> Kirim Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header-auto-show');
            if (header) {
                header.classList.add('header-active');
            }
        });
    </script>
    <script>
        const routeNomorOrtu = "{{ route('admin.surat-panggilan.api.nomor-ortu') }}";

        async function bukaModalWa(suratId, namaSiswa, siswaId) {
            // Reset dulu
            document.getElementById('wa-modal-nama').textContent = namaSiswa;
            document.getElementById('wa-input-nomor').value = '';
            document.getElementById('wa-list-nomor').innerHTML = '';
            document.getElementById('wa-pilihan-nomor').style.display = 'none';
            const kirimWaTemplate =
                "{{ route('admin.surat-panggilan.kirim-wa', ['suratPanggilan' => '__ID__']) }}";

            document.getElementById('form-kirim-wa').action =
                kirimWaTemplate.replace('__ID__', suratId);

            document.getElementById('modal-kirim-wa').style.display = 'flex';

            // Fetch nomor dari DB
            if (siswaId) {
                document.getElementById('wa-loading').style.display = 'inline';
                try {
                    const res = await fetch(routeNomorOrtu + '?siswa_id=' + siswaId);
                    const data = await res.json();

                    const opsi = [];

                    if (data.no_hp_ortu1) opsi.push({
                        label: (data.nama_ortu1 || 'Orang Tua 1'),
                        nomor: data.no_hp_ortu1,
                        ikon: 'fas fa-user',
                        warna: '#eff6ff',
                        border: '#bfdbfe',
                        teks: '#1d4ed8',
                    });
                    if (data.no_hp_ortu2) opsi.push({
                        label: (data.nama_ortu2 || 'Orang Tua 2'),
                        nomor: data.no_hp_ortu2,
                        ikon: 'fas fa-user',
                        warna: '#fdf4ff',
                        border: '#e9d5ff',
                        teks: '#7c3aed',
                    });
                    if (data.nama_wali && data.no_hp_ortu2) opsi.push({
                        // nama_wali tapi nomor bisa sama, hanya label beda
                        label: data.nama_wali,
                        nomor: data.no_hp_ortu2,
                        ikon: 'fas fa-user-shield',
                        warna: '#fff7ed',
                        border: '#fed7aa',
                        teks: '#c2410c',
                    });

                    if (opsi.length > 0) {
                        const container = document.getElementById('wa-list-nomor');
                        opsi.forEach(o => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.style.cssText = `
                                background:${o.warna};color:${o.teks};border:1px solid ${o.border};
                                border-radius:8px;padding:8px 12px;cursor:pointer;text-align:left;
                                display:flex;align-items:center;gap:10px;font-size:.82rem;width:100%;
                            `;
                            btn.innerHTML = `
                                <i class="${o.ikon}" style="font-size:.9rem;flex-shrink:0;"></i>
                                <div>
                                    <div style="font-weight:600;">${o.label}</div>
                                    <div style="font-size:.75rem;opacity:.8;letter-spacing:.5px;">${o.nomor}</div>
                                </div>
                                <i class="fas fa-arrow-right" style="margin-left:auto;opacity:.5;"></i>
                            `;
                            btn.onclick = () => {
                                document.getElementById('wa-input-nomor').value = o.nomor;
                                // Highlight input sebentar
                                document.getElementById('wa-input-nomor').style.borderColor = '#22c55e';
                                setTimeout(() => {
                                    document.getElementById('wa-input-nomor').style.borderColor =
                                    '';
                                }, 1000);
                            };
                            container.appendChild(btn);
                        });
                        document.getElementById('wa-pilihan-nomor').style.display = 'block';
                    }
                } catch (e) {
                    console.warn('Gagal fetch nomor ortu:', e);
                } finally {
                    document.getElementById('wa-loading').style.display = 'none';
                }
            }
        }

        function tutupModalWa() {
            document.getElementById('modal-kirim-wa').style.display = 'none';
        }

        document.getElementById('modal-kirim-wa').addEventListener('click', function(e) {
            if (e.target === this) tutupModalWa();
        });
    </script>
@endpush
