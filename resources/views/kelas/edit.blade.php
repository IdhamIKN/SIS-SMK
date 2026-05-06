@extends('layouts.app')

@section('title', 'Edit Kelas')

@push('styles')
    @include('components.izin-styles')
    <style>
        /* ── Page strip ── */
        .page-strip {
            padding: 20px 20px 28px; position: relative; overflow: hidden;
        }
        .page-strip-kelas {
            background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 60%, #a78bfa 100%);
        }
        .page-strip::before {
            content: ''; position: absolute; top: -40px; right: -40px;
            width: 140px; height: 140px; background: rgba(255,255,255,.07); border-radius: 50%;
        }
        .page-strip::after {
            content: ''; position: absolute; bottom: -24px; left: -20px;
            width: 100px; height: 100px; background: rgba(255,255,255,.05); border-radius: 50%;
        }
        .live-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.15); backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,.2);
            padding: 3px 10px; border-radius: 20px;
            font-size: .7rem; font-weight: 600; color: rgba(255,255,255,.9);
            margin-bottom: 10px; position: relative; z-index: 1;
        }
        .live-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: #a7f3d0; display: inline-block; animation: pulse 2s infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1}50%{opacity:.4} }
        .page-strip h2 {
            font-size: 1.3rem; font-weight: 800; color: #fff; margin: 0 0 4px;
            position: relative; z-index: 1; display: flex; align-items: center; gap: 8px;
        }
        .page-strip p { font-size: .8rem; color: rgba(255,255,255,.7); margin: 0; position: relative; z-index: 1; }

        /* ── Alert ── */
        .alert { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border-radius: 12px; font-size: .84rem; margin: 12px 16px 0; }
        .a-err { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .a-err ul { margin: 4px 0 0 16px; font-size: .8rem; }

        /* ── Form card ── */
        .form-card {
            background: #fff; border: 1px solid var(--border, #e2e8f0);
            border-radius: 16px; margin: 12px 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04); overflow: hidden;
        }
        .form-card-head {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 16px 10px; border-bottom: 1px solid #f8fafc;
        }
        .form-card-head .fc-icon {
            width: 34px; height: 34px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0;
        }
        .form-card-head h3 { margin: 0; font-size: .9rem; font-weight: 700; }
        .form-card-body { padding: 16px; }

        /* ── Field ── */
        .fgroup { margin-bottom: 14px; }
        .form-label { display: block; font-size: .8rem; font-weight: 700; color: #0f172a; margin-bottom: 5px; }
        .form-label .req { color: #ef4444; margin-left: 2px; }
        .form-input {
            width: 100%; padding: 10px 12px;
            border: 1.5px solid var(--border, #e2e8f0); border-radius: 10px;
            font-size: .875rem; font-family: inherit; color: #0f172a; background: #f8fafc;
            outline: none; box-sizing: border-box; transition: border-color .2s, box-shadow .2s, background .2s;
            -webkit-appearance: none;
        }
        .form-input:focus { border-color: #7c3aed; background: #fff; box-shadow: 0 0 0 3px rgba(124,58,237,.1); }
        .form-input.is-error { border-color: #ef4444; }
        select.form-input {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center; padding-right: 32px;
        }
        .form-error { font-size: .72rem; color: #dc2626; margin-top: 4px; display: flex; align-items: center; gap: 4px; }
        .form-hint  { font-size: .7rem; color: #94a3b8; margin-top: 4px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        /* ── Tingkat radio cards ── */
        .tingkat-group { display: flex; gap: 8px; }
        .tingkat-card { flex: 1; position: relative; cursor: pointer; }
        .tingkat-card input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 2; margin: 0; }
        .tingkat-box {
            position: relative; z-index: 1; pointer-events: none;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;
            padding: 12px 6px; border: 1.5px solid var(--border, #e2e8f0);
            border-radius: 10px; background: #f8fafc; text-align: center; transition: all .18s;
        }
        .tingkat-box .tb-num { font-size: 1.1rem; font-weight: 900; color: #475569; }
        .tingkat-box .tb-lbl { font-size: .65rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; }
        .tingkat-card input:checked ~ .tingkat-box { border-color: #7c3aed; background: #ede9fe; }
        .tingkat-card input:checked ~ .tingkat-box .tb-num { color: #7c3aed; }
        .tingkat-card input:checked ~ .tingkat-box .tb-lbl { color: #7c3aed; }

        /* ── Shift radio ── */
        .shift-group { display: flex; gap: 8px; }
        .shift-card { flex: 1; position: relative; cursor: pointer; }
        .shift-card input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 2; margin: 0; }
        .shift-box {
            position: relative; z-index: 1; pointer-events: none;
            display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 10px 8px; border: 1.5px solid var(--border, #e2e8f0);
            border-radius: 10px; background: #f8fafc; font-size: .82rem; font-weight: 700;
            color: #475569; transition: all .18s;
        }
        .shift-card.pagi  input:checked ~ .shift-box { border-color: #f59e0b; background: #fef9c3; color: #a16207; }
        .shift-card.siang input:checked ~ .shift-box { border-color: #0ea5e9; background: #e0f2fe; color: #0369a1; }

        /* ── Action bar ── */
        .action-bar {
            position: fixed; bottom: var(--footer-h); left: 0; right: 0;
            padding: 10px 16px 12px;
            background: rgba(255,255,255,.96); backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0; display: flex; gap: 10px;
            z-index: 999; box-shadow: 0 -4px 20px rgba(0,0,0,.06);
        }
        .ab-btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 12px 16px; border-radius: 12px; font-size: .875rem; font-weight: 700; border: none; cursor: pointer; text-decoration: none; font-family: inherit; transition: all .18s; line-height: 1; }
        .ab-btn:active { transform: scale(.97); }
        .ab-btn-back    { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .ab-btn-back:hover { background: #e2e8f0; }
        .ab-btn-primary { background: #7c3aed; color: #fff; box-shadow: 0 3px 12px rgba(124,58,237,.3); }
        .ab-btn-primary:hover { filter: brightness(1.08); }

        .kelas-wrap { padding-bottom: calc(var(--footer-h) + 88px); }
    </style>
@endpush

@section('content')
<div class="kelas-wrap">

    {{-- Page Strip --}}
    <div class="page-strip page-strip-kelas">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ now()->translatedFormat('d F Y') }}
        </div>
        <h2><i class="fas fa-edit"></i> Edit Kelas</h2>
        <p>Perbarui informasi kelas {{ $kela->nama_kelas }}</p>
    </div>

    @if ($errors->any())
        <div class="alert a-err">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Silakan perbaiki:</strong>
                <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        </div>
    @endif

    <form id="editKelasForm" method="POST" action="{{ route('kelas.update', $kela) }}">
        @csrf
        @method('PUT')

        {{-- ① Info Kelas --}}
        <div class="form-card">
            <div class="form-card-head">
                <div class="fc-icon" style="background:#ede9fe; color:#7c3aed;"><i class="fas fa-chalkboard"></i></div>
                <h3>Informasi Kelas</h3>
            </div>
            <div class="form-card-body">

                {{-- Nama Kelas --}}
                <div class="fgroup">
                    <label class="form-label" for="nama_kelas">Nama Kelas <span class="req">*</span></label>
                    <input type="text" id="nama_kelas" name="nama_kelas"
                        class="form-input @error('nama_kelas') is-error @enderror"
                        value="{{ old('nama_kelas', $kela->nama_kelas) }}"
                        placeholder="Contoh: X TOT 1" maxlength="50" required>
                    @error('nama_kelas')<div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>

                {{-- Tingkat --}}
                <div class="fgroup">
                    <label class="form-label">Tingkat <span class="req">*</span></label>
                    <div class="tingkat-group">
                        @foreach (['X' => 'Sepuluh', 'XI' => 'Sebelas', 'XII' => 'Dua Belas'] as $val => $lbl)
                            <label class="tingkat-card">
                                <input type="radio" name="tingkat" value="{{ $val }}"
                                    {{ old('tingkat', $kela->tingkat) === $val ? 'checked' : '' }} required>
                                <div class="tingkat-box">
                                    <div class="tb-num">{{ $val }}</div>
                                    <div class="tb-lbl">{{ $lbl }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('tingkat')<div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>

                {{-- Shift --}}
                <div class="fgroup">
                    <label class="form-label">Shift <span class="req">*</span></label>
                    <div class="shift-group">
                        <label class="shift-card pagi">
                            <input type="radio" name="shift" value="Pagi"
                                {{ old('shift', $kela->shift) === 'Pagi' ? 'checked' : '' }} required>
                            <div class="shift-box"><i class="fas fa-sun"></i> Pagi</div>
                        </label>
                        <label class="shift-card siang">
                            <input type="radio" name="shift" value="Siang"
                                {{ old('shift', $kela->shift) === 'Siang' ? 'checked' : '' }}>
                            <div class="shift-box"><i class="fas fa-cloud-sun"></i> Siang</div>
                        </label>
                    </div>
                    @error('shift')<div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>

                {{-- Jurusan --}}
                <div class="fgroup">
                    <label class="form-label" for="jurusan_id">Jurusan <span class="req">*</span></label>
                    <select id="jurusan_id" name="jurusan_id"
                        class="form-input @error('jurusan_id') is-error @enderror" required>
                        <option value="">— Pilih Jurusan —</option>
                        @foreach ($jurusans as $j)
                            <option value="{{ $j->id }}" {{ old('jurusan_id', $kela->jurusan_id) == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                    @error('jurusan_id')<div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>

                {{-- Tahun Ajaran --}}
                <div class="fgroup">
                    <label class="form-label" for="tahun_ajaran">Tahun Ajaran</label>
                    <input type="text" id="tahun_ajaran" name="tahun_ajaran"
                        class="form-input @error('tahun_ajaran') is-error @enderror"
                        value="{{ old('tahun_ajaran', $kela->tahun_ajaran) }}"
                        placeholder="Contoh: 2025/2026" maxlength="20">
                    @error('tahun_ajaran')<div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

        {{-- ② Pengampu --}}
        <div class="form-card">
            <div class="form-card-head">
                <div class="fc-icon" style="background:#dcfce7; color:#15803d;"><i class="fas fa-chalkboard-teacher"></i></div>
                <h3>Wali Kelas &amp; BK</h3>
            </div>
            <div class="form-card-body">

                {{-- Wali Kelas --}}
                <div class="fgroup">
                    <label class="form-label" for="wali_kelas_id">Wali Kelas</label>
                    <select id="wali_kelas_id" name="wali_kelas_id"
                        class="form-input @error('wali_kelas_id') is-error @enderror">
                        <option value="">— Belum ditentukan —</option>
                        @foreach ($gtks as $g)
                            <option value="{{ $g->id }}" {{ old('wali_kelas_id', $kela->wali_kelas_id) == $g->id ? 'selected' : '' }}>
                                {{ $g->nama_lengkap }}{{ $g->kd_guru ? ' (' . $g->kd_guru . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('wali_kelas_id')<div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>

                {{-- Guru BK --}}
                <div class="fgroup">
                    <label class="form-label" for="bk_id">Guru BK</label>
                    <select id="bk_id" name="bk_id"
                        class="form-input @error('bk_id') is-error @enderror">
                        <option value="">— Belum ditentukan —</option>
                        @foreach ($gtks as $g)
                            <option value="{{ $g->id }}" {{ old('bk_id', $kela->bk_id) == $g->id ? 'selected' : '' }}>
                                {{ $g->nama_lengkap }}{{ $g->kd_guru ? ' (' . $g->kd_guru . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('bk_id')<div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

        {{-- ③ Kontak --}}
        <div class="form-card">
            <div class="form-card-head">
                <div class="fc-icon" style="background:#dcfce7; color:#15803d;"><i class="fab fa-whatsapp"></i></div>
                <h3>Grup WhatsApp</h3>
                <span style="font-size:.68rem; font-weight:700; padding:3px 9px; border-radius:20px; background:#f1f5f9; color:#64748b;">Opsional</span>
            </div>
            <div class="form-card-body">
                <div class="fgroup">
                    <label class="form-label" for="wa_group">Link / Nomor Grup WA</label>
                    <input type="text" id="wa_group" name="wa_group"
                        class="form-input @error('wa_group') is-error @enderror"
                        value="{{ old('wa_group', $kela->wa_group) }}"
                        placeholder="https://chat.whatsapp.com/...">
                    <div class="form-hint">Tempel link undangan grup WhatsApp kelas</div>
                    @error('wa_group')<div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

    </form>
</div>

{{-- Action Bar --}}
<div class="action-bar">
    <a href="{{ route('kelas.show', $kela) }}" class="ab-btn ab-btn-back">
        <i class="fas fa-times"></i> Batal
    </a>
    <button type="submit" form="editKelasForm" class="ab-btn ab-btn-primary">
        <i class="fas fa-save"></i> Simpan Perubahan
    </button>
</div>
@endsection