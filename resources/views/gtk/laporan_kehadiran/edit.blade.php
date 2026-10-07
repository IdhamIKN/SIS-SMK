@extends('layouts.app')

@section('title', 'Edit Laporan Kehadiran')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ── Wrapper ── */
        .lkg-wrap { padding: 0 12px; max-width: 720px; margin: 0 auto; box-sizing: border-box; }
        @media(min-width:768px)  { .lkg-wrap { padding: 0 20px; } }
        @media(min-width:1024px) { .lkg-wrap { padding: 0 24px; } }

        /* ── Info jadwal box ── */
        .jadwal-info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 14px;
            font-size: .82rem;
            color: #475569;
            line-height: 1.7;
        }
        .jadwal-info-box strong { color: #0f172a; display: block; font-size: .88rem; margin-bottom: 4px; }

        /* ── Card ── */
        .form-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .c-head { display:flex; align-items:center; gap:10px; padding:12px 14px; border-bottom:1px solid #f1f5f9; }
        @media(min-width:768px) { .c-head { padding:14px 18px; } }
        .c-head h3 { font-size:.9rem; font-weight:800; color:#0f172a; margin:0; flex:1; }
        .c-body { padding: 14px 16px; }
        @media(min-width:768px) { .c-body { padding: 16px 18px; } }

        /* ── Status radio grid ── */
        .status-radio-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        @media(min-width:600px) {
            .status-radio-group { grid-template-columns: repeat(3, 1fr); }
        }
        @media(min-width:900px) {
            .status-radio-group { grid-template-columns: repeat(4, 1fr); }
        }

        .status-radio { position: relative; cursor: pointer; }
        .status-radio input[type="radio"] { position:absolute; opacity:0; width:0; height:0; }

        .status-option {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 12px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            background: #fff;
            transition: all .15s;
            cursor: pointer;
            height: 100%;
            box-sizing: border-box;
        }
        .status-option:hover { box-shadow: 0 3px 10px rgba(0,0,0,.06); transform: translateY(-1px); }
        .status-radio input[type="radio"]:checked + .status-option {
            border-color: var(--sc);
            background: var(--sb);
            box-shadow: 0 4px 14px rgba(0,0,0,.07);
        }

        .so-dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: var(--sc); flex-shrink: 0;
        }
        .so-header { display: flex; align-items: center; gap: 7px; }
        .so-label { font-weight: 700; color: #1e293b; font-size: .83rem; }
        .so-desc  { font-size: .7rem; color: #64748b; line-height: 1.4; }

        /* ── Textarea ── */
        .form-label { display:block; font-size:.8rem; font-weight:700; color:#0f172a; margin-bottom:6px; }
        .form-input {
            width: 100%; padding: 10px 12px;
            border: 1.5px solid #e2e8f0; border-radius: 9px;
            font-size: .875rem; font-family: inherit; color: #0f172a;
            background: #f8fafc; resize: vertical; box-sizing: border-box; outline: none;
        }
        .form-input:focus { border-color: #f59e0b; background: #fff; box-shadow: 0 0 0 3px rgba(245,158,11,.1); }

        /* ── Action bar ── */
        .action-bar { position:fixed; bottom:var(--footer-h,0); left:0; right:0; padding:10px 12px 12px; background:rgba(255,255,255,.96); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); border-top:1px solid #e2e8f0; display:flex; gap:8px; z-index:999; box-shadow:0 -4px 20px rgba(0,0,0,.06); }
        @media(min-width:768px) { .action-bar { padding:10px 24px 12px; gap:12px; justify-content:flex-end; } }
        .ab-btn { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:12px 14px; border-radius:12px; font-size:.82rem; font-weight:700; border:none; cursor:pointer; text-decoration:none; font-family:inherit; transition:all .18s; line-height:1; white-space:nowrap; }
        @media(min-width:768px) { .ab-btn { flex:unset; min-width:130px; font-size:.875rem; padding:12px 20px; } }
        .ab-btn:active { transform: scale(.97); }
        .ab-btn-back  { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
        .ab-btn-green { background:#059669; color:#fff; box-shadow:0 3px 12px rgba(5,150,105,.3); }
    </style>
@endpush

@section('content')
<div class="event-wrap lkg-wrap" style="padding-top:var(--header-h,56px);padding-bottom: 148px;">

    {{-- Page Strip --}}
    <div class="page-strip page-strip-event">
        <div class="live-badge"><span class="live-dot"></span>Edit Laporan</div>
        <h2><i class="fas fa-pen"></i> Edit Laporan Kehadiran</h2>
        <p>Perbarui status kehadiran guru</p>
    </div>

    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Info Jadwal ── ringkas & responsif ── --}}
    <div class="jadwal-info-box">
        <strong>
            <i class="fas fa-info-circle" style="color:#f59e0b;margin-right:4px;"></i>
            Jam {{ $laporanKehadiran->jam_ke }}
            @if($laporanKehadiran->jadwalKbm?->mata_pelajaran)
                — {{ $laporanKehadiran->jadwalKbm->mata_pelajaran }}
            @endif
        </strong>
        <span>
            <i class="fas fa-user-tie" style="margin-right:3px;"></i>{{ $laporanKehadiran->gtk?->nama_lengkap ?? '-' }}
            &nbsp;·&nbsp;
            <i class="fas fa-chalkboard" style="margin-right:3px;"></i>{{ $laporanKehadiran->kelas?->nama_kelas ?? '-' }}
            &nbsp;·&nbsp;
            <i class="fas fa-calendar" style="margin-right:3px;"></i>{{ $laporanKehadiran->tanggal->translatedFormat('d F Y') }}
            @if($laporanKehadiran->jadwalKbm?->jam_mulai)
                &nbsp;·&nbsp;
                <i class="fas fa-clock" style="margin-right:3px;"></i>
                {{ \Carbon\Carbon::parse($laporanKehadiran->jadwalKbm->jam_mulai)->format('H:i') }}
                –
                {{ \Carbon\Carbon::parse($laporanKehadiran->jadwalKbm->jam_selesai)->format('H:i') }}
            @endif
        </span>
    </div>

    <form id="edit-form" method="POST" action="{{ route('kehadiran-guru.update', $laporanKehadiran) }}"
          data-mata-pelajaran="{{ $laporanKehadiran->jadwalKbm?->mata_pelajaran ?? '' }}"
          data-jam="{{ $laporanKehadiran->jam_ke }}">
        @csrf
        @method('PUT')

        {{-- Status ── --}}
        <div class="form-card">
            <div class="c-head">
                <div class="c-icon" style="background:#fef3c7;color:#b45309;"><i class="fas fa-circle-dot"></i></div>
                <h3>Status Kehadiran</h3>
            </div>
            <div class="c-body">
                @php
                    $statusOptions = [
                        'hijau'  => ['color'=>'#22c55e','bg'=>'#dcfce7','label'=>'Hadir Tepat Waktu',        'desc'=>'Guru hadir sesuai jadwal.'],
                        'kuning' => ['color'=>'#eab308','bg'=>'#fef9c3','label'=>'Hadir Terlambat',          'desc'=>'Guru hadir melewati jam mulai.'],
                        'merah'  => ['color'=>'#ef4444','bg'=>'#fee2e2','label'=>'Tidak Hadir',              'desc'=>'Guru tidak hadir dan tidak ada tugas.'],
                        'abu'    => ['color'=>'#64748b','bg'=>'#f1f5f9','label'=>'Tidak Hadir + Ada Tugas',  'desc'=>'Tidak hadir, namun memberikan tugas.'],
                        'biru'   => ['color'=>'#3b82f6','bg'=>'#dbeafe','label'=>'Pergi + Ada Tugas',        'desc'=>'Hadir lalu meninggalkan kelas dengan tugas.'],
                        'pink'   => ['color'=>'#ec4899','bg'=>'#fce7f3','label'=>'Pergi + No Tugas',         'desc'=>'Hadir lalu meninggalkan kelas tanpa tugas.'],
                        'orange' => ['color'=>'#f97316','bg'=>'#ffedd5','label'=>'Tanpa Laporan (Override)', 'desc'=>'Set status tanpa laporan secara manual.'],
                    ];
                @endphp
                <div class="status-radio-group">
                    @foreach($statusOptions as $val => $opt)
                        <label class="status-radio" style="--sc:{{ $opt['color'] }};--sb:{{ $opt['bg'] }};">
                            <input type="radio" name="status" value="{{ $val }}"
                                {{ old('status', $laporanKehadiran->status) === $val ? 'checked' : '' }} required>
                            <div class="status-option">
                                <div class="so-header">
                                    <div class="so-dot"></div>
                                    <div class="so-label">{{ $opt['label'] }}</div>
                                </div>
                                <div class="so-desc">{{ $opt['desc'] }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('status')
                    <p style="color:#dc2626;font-size:.78rem;margin-top:8px;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Catatan ── --}}
        <div class="form-card">
            <div class="c-head">
                <div class="c-icon" style="background:#e0f2fe;color:#0369a1;"><i class="fas fa-comment-alt"></i></div>
                <h3>Catatan <span style="font-weight:400;color:#94a3b8;font-size:.82rem;">(Opsional)</span></h3>
            </div>
            <div class="c-body">
                <textarea name="catatan" class="form-input" rows="3"
                    placeholder="Tambahkan catatan jika diperlukan..."
                    maxlength="500">{{ old('catatan', $laporanKehadiran->catatan) }}</textarea>
                @error('catatan')
                    <p style="color:#dc2626;font-size:.78rem;margin-top:4px;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                @enderror
                <div style="text-align:right;font-size:.7rem;color:#94a3b8;margin-top:4px;">Maks 500 karakter</div>
            </div>
        </div>

    </form>

</div>

{{-- Action Bar --}}
<div class="action-bar">
    <a href="{{ route('kehadiran-guru.show', $laporanKehadiran) }}" class="ab-btn ab-btn-back">
        <i class="fas fa-arrow-left"></i> Batal
    </a>
    <button type="button" id="btn-simpan" class="ab-btn ab-btn-green">
        <i class="fas fa-save"></i> Simpan Perubahan
    </button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form    = document.getElementById('edit-form');
    const btnSave = document.getElementById('btn-simpan');

    btnSave.addEventListener('click', function () {
        const radio = form.querySelector('input[name="status"]:checked');
        if (!radio) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum Memilih Status',
                text: 'Silakan pilih status kehadiran guru terlebih dahulu.',
                confirmButtonColor: '#059669',
            });
            return;
        }

        const mapel       = form.dataset.mataPelajaran ?? '';
        const jam         = form.dataset.jam ?? '';
        const statusLabel = radio.closest('.status-radio')
                                 ?.querySelector('.so-label')?.textContent ?? radio.value;

        Swal.fire({
            icon: 'question',
            title: 'Simpan Perubahan?',
            html: `Perbarui laporan <strong>Jam ${jam}${mapel ? ' – ' + mapel : ''}</strong>?<br>
                   <small style="color:#6b7280;">Status baru: <strong>${statusLabel}</strong></small>`,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#059669',
            cancelButtonColor: '#94a3b8',
        }).then(function (r) {
            if (r.isConfirmed) {
                btnSave.disabled = true;
                btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                form.submit();
            }
        });
    });
});
</script>
@endpush
