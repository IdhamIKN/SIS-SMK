@extends('layouts.app')

@section('title', 'Konfigurasi Sekolah')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css"/>
{{-- SweetAlert2 --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
/* Ensure footer-bar is visible on school config page */
#footer-bar {
    z-index: 999 !important;
    position: fixed !important;
    bottom: 0 !important;
    left: 0 !important;
    right: 0 !important;
    display: flex !important;
    opacity: 1 !important;
    visibility: visible !important;
    transform: none !important;
    min-height: 60px !important;
}

/* Set CSS variable as fallback */
.scfg-pg {
    --footer-h: 60px;
}
</style>
<style>
/* scope: .scfg-pg — tidak ada selector tanpa prefix ini */
.scfg-pg { font-family: inherit; }

.scfg-pg .scfg-strip {
    padding: 20px 20px 28px;
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0ea5e9 100%);
    position: relative; overflow: hidden;
}
.scfg-pg .scfg-strip::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:140px; height:140px; background:rgba(255,255,255,.06); border-radius:50%;
}
.scfg-pg .scfg-strip::after {
    content:''; position:absolute; bottom:-24px; left:-20px;
    width:100px; height:100px; background:rgba(255,255,255,.04); border-radius:50%;
}
.scfg-pg .scfg-live {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.18);
    padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:600;
    color:rgba(255,255,255,.9); margin-bottom:10px; position:relative; z-index:1;
}
.scfg-pg .scfg-dot {
    width:7px; height:7px; border-radius:50%;
    background:#7dd3fc; display:inline-block; animation:scfg-pulse 2s infinite;
}
@keyframes scfg-pulse{0%,100%{opacity:1}50%{opacity:.4}}
.scfg-pg .scfg-strip h2 {
    font-size:1.3rem; font-weight:800; color:#fff; margin:0 0 4px;
    position:relative; z-index:1; display:flex; align-items:center; gap:8px;
}
.scfg-pg .scfg-strip p {
    font-size:.8rem; color:rgba(255,255,255,.65); margin:0; position:relative; z-index:1;
}

/* alerts */
.scfg-pg .scfg-ok, .scfg-pg .scfg-err {
    display:flex; align-items:flex-start; gap:10px;
    padding:12px 14px; border-radius:12px; font-size:.84rem; margin:12px 16px 0;
}
.scfg-pg .scfg-ok  { background:#dcfce7; color:#15803d; border:1px solid #86efac; }
.scfg-pg .scfg-err { background:#fee2e2; color:#dc2626; border:1px solid #fecaca; }
.scfg-pg .scfg-err ul { margin:4px 0 0 16px; font-size:.8rem; }

/* card */
.scfg-pg .scfg-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:16px;
    margin:12px 16px; box-shadow:0 1px 4px rgba(0,0,0,.04); overflow:hidden;
}
.scfg-pg .scfg-chead {
    display:flex; align-items:center; gap:10px;
    padding:13px 16px 11px; border-bottom:1px solid #f8fafc;
}
.scfg-pg .scfg-cico {
    width:34px; height:34px; border-radius:10px;
    display:flex; align-items:center; justify-content:center; font-size:.9rem; flex-shrink:0;
}
.scfg-pg .scfg-chead h3 { margin:0; font-size:.9rem; font-weight:700; flex:1; }
.scfg-pg .scfg-opt {
    font-size:.65rem; font-weight:700; padding:2px 8px; border-radius:20px;
    background:#f1f5f9; color:#64748b;
}
.scfg-pg .scfg-cbody { padding:16px; }

/* fields */
.scfg-pg .scfg-fg { margin-bottom:14px; }
.scfg-pg .scfg-lbl {
    display:block; font-size:.8rem; font-weight:700; color:#0f172a; margin-bottom:5px;
}
.scfg-pg .scfg-inp {
    width:100%; padding:10px 12px;
    border:1.5px solid #e2e8f0; border-radius:10px;
    font-size:.875rem; font-family:inherit; color:#0f172a; background:#f8fafc;
    outline:none; box-sizing:border-box; -webkit-appearance:none;
    transition:border-color .2s, box-shadow .2s, background .2s;
}
.scfg-pg .scfg-inp:focus { border-color:#0ea5e9; background:#fff; box-shadow:0 0 0 3px rgba(14,165,233,.1); }
.scfg-pg .scfg-inp[readonly] { background:#f1f5f9; color:#64748b; cursor:default; }
.scfg-pg .scfg-inp.iserr { border-color:#ef4444; }
.scfg-pg .scfg-ferr { font-size:.72rem; color:#dc2626; margin-top:4px; display:flex; align-items:center; gap:4px; }
.scfg-pg .scfg-hint { font-size:.7rem; color:#94a3b8; margin-top:4px; }
.scfg-pg .scfg-g2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

/* hari efektif */
.scfg-pg .scfg-hari-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.scfg-pg .scfg-hcard { position:relative; cursor:pointer; }
.scfg-pg .scfg-hcard input[type="checkbox"] {
    position:absolute; inset:0; width:100%; height:100%;
    opacity:0; cursor:pointer; z-index:2; margin:0;
}
.scfg-pg .scfg-hbox {
    position:relative; z-index:1; pointer-events:none;
    display:flex; align-items:center; gap:8px; padding:10px 12px;
    border:1.5px solid #e2e8f0; border-radius:10px; background:#f8fafc;
    font-size:.82rem; font-weight:600; color:#475569; transition:all .18s;
}
.scfg-pg .scfg-hbox .shd {
    width:8px; height:8px; border-radius:50%; background:#e2e8f0;
    flex-shrink:0; transition:background .18s;
}
.scfg-pg .scfg-hcard input:checked ~ .scfg-hbox {
    border-color:#0ea5e9; background:#e0f2fe; color:#0369a1;
}
.scfg-pg .scfg-hcard input:checked ~ .scfg-hbox .shd { background:#0ea5e9; }

/* map */
.scfg-pg .scfg-mwrap {
    border-radius:12px; overflow:hidden; border:1px solid #e2e8f0; margin-bottom:14px;
}
.scfg-pg #scfgMap { width:100%; height:260px; z-index:1; }
.scfg-pg .scfg-mstatus {
    padding:8px 12px; background:#f8fafc; border-top:1px solid #e2e8f0;
    font-size:.75rem; color:#64748b;
    display:flex; justify-content:space-between; align-items:center;
}
.scfg-pg .scfg-clrbtn {
    display:inline-flex; align-items:center; gap:5px;
    padding:7px 12px; border-radius:8px; font-size:.78rem; font-weight:600;
    background:#fef2f2; color:#dc2626; border:1px solid #fecaca;
    cursor:pointer; font-family:inherit; transition:background .18s;
}
.scfg-pg .scfg-clrbtn:hover { background:#fee2e2; }

/* action bar — z-index 100 agar tidak menutup sidebar */
.scfg-pg .scfg-bar {
    position:fixed; bottom:var(--footer-h, 60px); left:0; right:0;
    padding:10px 16px 12px;
    background:rgba(255,255,255,.96); backdrop-filter:blur(10px);
    border-top:1px solid #e2e8f0; display:flex; gap:10px;
    z-index:100; box-shadow:0 -4px 20px rgba(0,0,0,.06);
}
.scfg-pg .scfg-sbtn {
    flex:1; display:inline-flex; align-items:center; justify-content:center;
    gap:7px; padding:12px 16px; border-radius:12px; font-size:.9rem;
    font-weight:700; border:none; cursor:pointer; font-family:inherit;
    background:linear-gradient(135deg,#0369a1,#0ea5e9);
    color:#fff; box-shadow:0 3px 12px rgba(14,165,233,.3); transition:filter .18s;
}
.scfg-pg .scfg-sbtn:hover { filter:brightness(1.08); }
</style>
@endpush

@section('content')
<div class="scfg-pg" style="padding-bottom:calc(var(--footer-h,60px) + 88px);">

    {{-- Strip --}}
    <div class="scfg-strip">
        <div class="scfg-live"><span class="scfg-dot"></span>Pengaturan Sistem</div>
        <h2><i class="fas fa-cog"></i> Konfigurasi Sekolah</h2>
        <p>Atur jam, hari efektif, dan lokasi presensi sekolah</p>
    </div>



    <form id="scfgForm" action="{{ route('admin.school-config.update') }}" method="POST">
        @csrf @method('PUT')

        {{-- Identitas Sistem --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-school"></i></div>
                <h3>Identitas Sistem</h3>
            </div>
            <div class="scfg-cbody">
                <div class="scfg-fg">
                    <label class="scfg-lbl" for="system_name">Nama Sistem</label>
                    <input type="text" id="system_name" name="system_name"
                        class="scfg-inp @error('system_name') iserr @enderror"
                        value="{{ old('system_name', $sekolah->system_name ?? 'SIS SMKN 5 Madiun') }}"
                        placeholder="Nama sistem aplikasi" maxlength="100">
                    <div class="scfg-hint">Ditampilkan di header dan halaman login</div>
                    @error('system_name')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Identitas Sekolah --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-building"></i></div>
                <h3>Identitas Sekolah</h3>
            </div>
            <div class="scfg-cbody">
                <div class="scfg-fg">
                    <label class="scfg-lbl" for="sekolah">Nama Sekolah</label>
                    <input type="text" id="sekolah" name="sekolah"
                        class="scfg-inp @error('sekolah') iserr @enderror"
                        value="{{ old('sekolah', $sekolah->sekolah) }}"
                        placeholder="Nama lengkap sekolah" maxlength="255">
                    @error('sekolah')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>

                <div class="scfg-fg">
                    <label class="scfg-lbl" for="alsekolah">Alamat Sekolah</label>
                    <textarea id="alsekolah" name="alsekolah" rows="3"
                        class="scfg-inp @error('alsekolah') iserr @enderror"
                        placeholder="Alamat lengkap sekolah">{{ old('alsekolah', $sekolah->alsekolah) }}</textarea>
                    @error('alsekolah')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>

                <div class="scfg-g2">
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="telp">Telepon</label>
                        <input type="text" id="telp" name="telp"
                            class="scfg-inp @error('telp') iserr @enderror"
                            value="{{ old('telp', $sekolah->telp) }}"
                            placeholder="021-12345678" maxlength="20">
                        @error('telp')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>

                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="email">Email</label>
                        <input type="email" id="email" name="email"
                            class="scfg-inp @error('email') iserr @enderror"
                            value="{{ old('email', $sekolah->email) }}"
                            placeholder="info@sekolah.sch.id" maxlength="255">
                        @error('email')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="scfg-g2">
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="kab">Kabupaten</label>
                        <input type="text" id="kab" name="kab"
                            class="scfg-inp @error('kab') iserr @enderror"
                            value="{{ old('kab', $sekolah->kab) }}"
                            placeholder="Kabupaten Madiun" maxlength="100">
                        @error('kab')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>

                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="alias">Alias</label>
                        <input type="text" id="alias" name="alias"
                            class="scfg-inp @error('alias') iserr @enderror"
                            value="{{ old('alias', $sekolah->alias) }}"
                            placeholder="SMKN5MDN" maxlength="50">
                        @error('alias')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Kepala Sekolah --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#fed7d7;color:#c53030;"><i class="fas fa-user-tie"></i></div>
                <h3>Kepala Sekolah</h3>
            </div>
            <div class="scfg-cbody">
                <div class="scfg-g2">
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="nama_ks">Nama Kepala Sekolah</label>
                        <input type="text" id="nama_ks" name="nama_ks"
                            class="scfg-inp @error('nama_ks') iserr @enderror"
                            value="{{ old('nama_ks', $sekolah->nama_ks) }}"
                            placeholder="Dr. H. Ahmad Yani, M.Pd." maxlength="255">
                        @error('nama_ks')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>

                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="nip_ks">NIP Kepala Sekolah</label>
                        <input type="text" id="nip_ks" name="nip_ks"
                            class="scfg-inp @error('nip_ks') iserr @enderror"
                            value="{{ old('nip_ks', $sekolah->nip_ks) }}"
                            placeholder="198001012010011001" maxlength="50">
                        @error('nip_ks')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Wakil Kepala Sekolah --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#feebc8;color:#d69e2e;"><i class="fas fa-user-graduate"></i></div>
                <h3>Wakil Kepala Sekolah</h3>
            </div>
            <div class="scfg-cbody">
                <div class="scfg-g2">
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="nama_waka">Nama Wakil Kepala Sekolah</label>
                        <input type="text" id="nama_waka" name="nama_waka"
                            class="scfg-inp @error('nama_waka') iserr @enderror"
                            value="{{ old('nama_waka', $sekolah->nama_waka) }}"
                            placeholder="Drs. Siti Aminah, M.Pd." maxlength="255">
                        @error('nama_waka')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>

                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="nip_waka">NIP Wakil Kepala Sekolah</label>
                        <input type="text" id="nip_waka" name="nip_waka"
                            class="scfg-inp @error('nip_waka') iserr @enderror"
                            value="{{ old('nip_waka', $sekolah->nip_waka) }}"
                            placeholder="198501022010012002" maxlength="50">
                        @error('nip_waka')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Ketua --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#c4f1f9;color:#0e7490;"><i class="fas fa-user-cog"></i></div>
                <h3>Ketua</h3>
            </div>
            <div class="scfg-cbody">
                <div class="scfg-g2">
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="nama_ketua">Nama Ketua</label>
                        <input type="text" id="nama_ketua" name="nama_ketua"
                            class="scfg-inp @error('nama_ketua') iserr @enderror"
                            value="{{ old('nama_ketua', $sekolah->nama_ketua) }}"
                            placeholder="Ir. Budi Santoso, MT." maxlength="255">
                        @error('nama_ketua')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>

                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="nip_ketua">NIP Ketua</label>
                        <input type="text" id="nip_ketua" name="nip_ketua"
                            class="scfg-inp @error('nip_ketua') iserr @enderror"
                            value="{{ old('nip_ketua', $sekolah->nip_ketua) }}"
                            placeholder="197801032008011003" maxlength="50">
                        @error('nip_ketua')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Website & Media --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#e0e7ff;color:#3730a3;"><i class="fas fa-globe"></i></div>
                <h3>Website & Media</h3>
            </div>
            <div class="scfg-cbody">
                <div class="scfg-g2">
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="site_url">URL Website</label>
                        <input type="url" id="site_url" name="site_url"
                            class="scfg-inp @error('site_url') iserr @enderror"
                            value="{{ old('site_url', $sekolah->site_url) }}"
                            placeholder="https://smkn5madiun.sch.id" maxlength="255">
                        @error('site_url')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>

                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="site_logo">URL Logo</label>
                        <input type="url" id="site_logo" name="site_logo"
                            class="scfg-inp @error('site_logo') iserr @enderror"
                            value="{{ old('site_logo', $sekolah->site_logo) }}"
                            placeholder="https://smkn5madiun.sch.id/logo.png" maxlength="255">
                        @error('site_logo')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="scfg-fg">
                    <label class="scfg-lbl" for="wasekolah">WhatsApp Sekolah</label>
                    <input type="text" id="wasekolah" name="wasekolah"
                        class="scfg-inp @error('wasekolah') iserr @enderror"
                        value="{{ old('wasekolah', $sekolah->wasekolah) }}"
                        placeholder="6281234567890" maxlength="20">
                    <div class="scfg-hint">Nomor WhatsApp tanpa tanda +</div>
                    @error('wasekolah')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Jam Sekolah --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#fef3c7;color:#b45309;"><i class="fas fa-clock"></i></div>
                <h3>Jam Sekolah</h3>
            </div>
            <div class="scfg-cbody">
                <div class="scfg-g2">
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="jam_masuk">Jam Masuk</label>
                        <input type="time" id="jam_masuk" name="jam_masuk"
                            class="scfg-inp @error('jam_masuk') iserr @enderror"
                            value="{{ old('jam_masuk', $sekolah->jam_masuk) }}">
                        @error('jam_masuk')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>
                    <div class="scfg-fg">
                        <label class="scfg-lbl" for="jam_pulang">Jam Pulang</label>
                        <input type="time" id="jam_pulang" name="jam_pulang"
                            class="scfg-inp @error('jam_pulang') iserr @enderror"
                            value="{{ old('jam_pulang', $sekolah->jam_pulang) }}">
                        @error('jam_pulang')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Hari Efektif --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-calendar-week"></i></div>
                <h3>Hari Efektif Sekolah</h3>
            </div>
            <div class="scfg-cbody">
                <div class="scfg-hint" style="margin-bottom:10px;">Centang hari-hari masuk sekolah</div>
                @php
                    $hariList     = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
                                    $selectedHari = old('hari_efektif', is_string($sekolah->hari_efektif) ? json_decode($sekolah->hari_efektif, true) : ($sekolah->hari_efektif ?? []));
                @endphp
                <div class="scfg-hari-grid">
                    @foreach($hariList as $h)
                        <label class="scfg-hcard">
                            <input type="checkbox" name="hari_efektif[]" value="{{ $h }}"
                                {{ in_array($h, $selectedHari) ? 'checked' : '' }}>
                            <div class="scfg-hbox"><span class="shd"></span>{{ $h }}</div>
                        </label>
                    @endforeach
                </div>
                @error('hari_efektif')<div class="scfg-ferr" style="margin-top:8px;"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Lokasi Presensi --}}
        <div class="scfg-card">
            <div class="scfg-chead">
                <div class="scfg-cico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-map-marker-alt"></i></div>
                <h3>Lokasi Presensi</h3>
                <span class="scfg-opt">Opsional</span>
            </div>
            <div class="scfg-cbody">
                <div class="scfg-hint" style="margin-bottom:12px;line-height:1.55;">
                    Siswa harus berada dalam radius lokasi ini saat melakukan presensi.
                </div>
                <div class="scfg-mwrap">
                    <div id="scfgMap"></div>
                    <div class="scfg-mstatus">
                        <span><i class="fas fa-mouse-pointer"></i> Klik peta atau gunakan pencarian</span>
                        <span id="scfgCoord">Lat: -, Lng: -</span>
                    </div>
                </div>
                <div class="scfg-g2" style="margin-bottom:10px;">
                    <div class="scfg-fg">
                        <label class="scfg-lbl">Latitude</label>
                        <input type="text" id="scfgLat" name="latitude"
                            class="scfg-inp @error('latitude') iserr @enderror"
                                               value="{{ old('latitude', $sekolah->latitude ?? '') }}"
                            placeholder="-7.6291" readonly>
                        @error('latitude')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>
                    <div class="scfg-fg">
                        <label class="scfg-lbl">Longitude</label>
                        <input type="text" id="scfgLng" name="longitude"
                            class="scfg-inp @error('longitude') iserr @enderror"
                                               value="{{ old('longitude', $sekolah->longitude ?? '') }}"
                            placeholder="111.5230" readonly>
                        @error('longitude')<div class="scfg-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
                <button type="button" class="scfg-clrbtn" onclick="scfgClear()">
                    <i class="fas fa-trash-alt"></i> Hapus Pin
                </button>
            </div>
        </div>

    </form>

    {{-- Action Bar --}}
    <div class="scfg-bar">
        <button type="submit" form="scfgForm" class="scfg-sbtn">
            <i class="fas fa-save"></i> Simpan Konfigurasi
        </button>
    </div>

</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js"></script>
    <script>
        // Ensure footer-bar remains visible when scrolling on school config page
        document.addEventListener('DOMContentLoaded', function() {
            const footerBar = document.getElementById('footer-bar');
            if (footerBar) {
                // Set CSS variable for footer height
                const footerHeight = footerBar.offsetHeight || 60;
                document.documentElement.style.setProperty('--footer-h', footerHeight + 'px');

                // Force footer to stay visible
                footerBar.style.display = 'flex';
                footerBar.style.opacity = '1';
                footerBar.style.visibility = 'visible';
                footerBar.style.transform = 'none';
                footerBar.style.zIndex = '999';

                // Monitor scroll events and keep footer visible
                window.addEventListener('scroll', function() {
                    footerBar.style.display = 'flex';
                    footerBar.style.opacity = '1';
                    footerBar.style.visibility = 'visible';
                    footerBar.style.transform = 'none';
                });

                // Also monitor window resize
                window.addEventListener('resize', function() {
                    const newFooterHeight = footerBar.offsetHeight || 60;
                    document.documentElement.style.setProperty('--footer-h', newFooterHeight + 'px');
                });
            }
        });
    </script>
<script>
(function(){
    var map, mkr, cir;

    function setVal(id, v) {
        var el = document.getElementById(id);
        el.removeAttribute('readonly'); el.value = v; el.setAttribute('readonly', true);
    }
    function updateCoord() {
        var lat = document.getElementById('scfgLat').value;
        var lng = document.getElementById('scfgLng').value;
        document.getElementById('scfgCoord').textContent = lat ? 'Lat: '+lat+', Lng: '+lng : 'Lat: -, Lng: -';
    }
    function setMkr(lat, lng) {
        if (mkr) map.removeLayer(mkr);
        if (cir) map.removeLayer(cir);
        mkr = L.marker([lat,lng]).addTo(map);
        cir = L.circle([lat,lng],{radius:100,color:'#0ea5e9',fillColor:'#0ea5e9',fillOpacity:.15,weight:2}).addTo(map);
        setVal('scfgLat', lat.toFixed(6)); setVal('scfgLng', lng.toFixed(6));
        updateCoord(); map.fitBounds(cir.getBounds(),{padding:[20,20]});
    }
    window.scfgClear = function() {
        if (mkr){map.removeLayer(mkr);mkr=null;}
        if (cir){map.removeLayer(cir);cir=null;}
        setVal('scfgLat',''); setVal('scfgLng',''); updateCoord();
    };

    document.addEventListener('DOMContentLoaded', function(){
        var sLat = document.getElementById('scfgLat').value;
        var sLng = document.getElementById('scfgLng').value;
        var lat = sLat ? parseFloat(sLat) : -7.6291;
        var lng = sLng ? parseFloat(sLng) : 111.5230;

        map = L.map('scfgMap').setView([lat,lng], sLat ? 15 : 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
            attribution:'&copy; OpenStreetMap contributors', maxZoom:19
        }).addTo(map);
        L.Control.geocoder({
            defaultMarkGeocode:false, placeholder:'Cari lokasi...',
            errorMessage:'Tidak ditemukan', suggestTimeout:250, queryMinLength:3
        }).on('markgeocode',function(e){
            map.setView(e.geocode.center,16); setMkr(e.geocode.center.lat,e.geocode.center.lng);
        }).addTo(map);
        map.on('click',function(e){ setMkr(e.latlng.lat,e.latlng.lng); });
        if (sLat && sLng) setMkr(lat, lng);
        updateCoord();
    });
})();

// SweetAlert2 for messages
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#16a34a',
        confirmButtonText: 'OK',
        timer: 4000,
        timerProgressBar: true,
    });
@endif

@if($errors->any())
    Swal.fire({
        icon: 'error',
        title: 'Validasi Gagal',
        html: '<ul style="text-align:left;padding-left:16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>',
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Tutup',
    });
@endif
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush