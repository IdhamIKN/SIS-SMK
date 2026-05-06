@extends('layouts.app')

@section('title', 'Profil Saya')

@push('styles')
{{-- SweetAlert2 --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
/* scope: .prf-pg — tidak ada selector tanpa prefix ini */
.prf-pg { font-family: inherit; }

/* hero */
.prf-pg .prf-hero {
    position:relative;
    background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 55%,#0ea5e9 100%);
    padding:24px 20px 60px; overflow:hidden;
}
.prf-pg .prf-hero::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:140px; height:140px; background:rgba(255,255,255,.06); border-radius:50%;
}
.prf-pg .prf-hero::after {
    content:''; position:absolute; bottom:-24px; left:-20px;
    width:100px; height:100px; background:rgba(255,255,255,.04); border-radius:50%;
}
.prf-pg .prf-hero-nav {
    position:relative; z-index:2;
    display:flex; align-items:center; gap:8px; margin-bottom:0;
}
.prf-pg .prf-live {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.18);
    padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:600;
    color:rgba(255,255,255,.9); position:relative; z-index:1;
}
.prf-pg .prf-dot {
    width:7px; height:7px; border-radius:50%;
    background:#7dd3fc; display:inline-block; animation:prf-pulse 2s infinite;
}
@keyframes prf-pulse{0%,100%{opacity:1}50%{opacity:.4}}

/* profile card overlap */
.prf-pg .prf-profile-card {
    position:relative; z-index:3;
    background:#fff; border-radius:20px;
    box-shadow:0 6px 28px rgba(0,0,0,.12);
    margin:-36px 16px 0; padding:18px;
    display:flex; align-items:center; gap:16px;
}
.prf-pg .prf-ava {
    width:68px; height:68px; border-radius:16px;
    border:3px solid #fff; box-shadow:0 2px 12px rgba(0,0,0,.12);
    flex-shrink:0; overflow:hidden; background:#dbeafe;
    display:flex; align-items:center; justify-content:center;
    color:#1d4ed8; font-size:1.6rem; position:relative;
}
.prf-pg .prf-ava img { width:100%; height:100%; object-fit:cover; }
.prf-pg .prf-ava-edit {
    position:absolute; bottom:-2px; right:-2px;
    width:22px; height:22px; border-radius:50%;
    background:#0ea5e9; color:#fff; font-size:.55rem;
    display:flex; align-items:center; justify-content:center;
    border:2px solid #fff; cursor:pointer; z-index:2;
}
.prf-pg .prf-uname { font-size:1rem; font-weight:800; color:#0f172a; margin:0 0 2px; }
.prf-pg .prf-urole { font-size:.72rem; color:#64748b; margin:0 0 6px; }
.prf-pg .prf-utag {
    display:inline-flex; align-items:center; gap:4px;
    font-size:.65rem; font-weight:700; padding:2px 8px; border-radius:20px;
    background:#dbeafe; color:#1d4ed8;
}

/* section body */
.prf-pg .prf-body { padding:14px 0 calc(var(--footer-h,60px) + 100px); }

/* cards */
.prf-pg .prf-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:16px;
    margin:0 16px 12px; box-shadow:0 1px 4px rgba(0,0,0,.04); overflow:hidden;
}
.prf-pg .prf-chead {
    display:flex; align-items:center; gap:10px;
    padding:13px 16px 11px; border-bottom:1px solid #f8fafc;
}
.prf-pg .prf-cico {
    width:34px; height:34px; border-radius:10px;
    display:flex; align-items:center; justify-content:center; font-size:.9rem; flex-shrink:0;
}
.prf-pg .prf-chead h3 { margin:0; font-size:.9rem; font-weight:700; }
.prf-pg .prf-cbody { padding:16px; }

/* divider */
.prf-pg .prf-divider { height:1px; background:#f1f5f9; margin:4px 0 16px; }

/* fields */
.prf-pg .prf-fg { margin-bottom:14px; }
.prf-pg .prf-lbl { display:block; font-size:.8rem; font-weight:700; color:#0f172a; margin-bottom:5px; }
.prf-pg .prf-inp {
    width:100%; padding:10px 12px;
    border:1.5px solid #e2e8f0; border-radius:10px;
    font-size:.875rem; font-family:inherit; color:#0f172a; background:#f8fafc;
    outline:none; box-sizing:border-box; -webkit-appearance:none;
    transition:border-color .2s, box-shadow .2s, background .2s;
}
.prf-pg .prf-inp:focus { border-color:#0ea5e9; background:#fff; box-shadow:0 0 0 3px rgba(14,165,233,.1); }
.prf-pg .prf-inp.iserr { border-color:#ef4444; }
.prf-pg .prf-inp-pw:focus { border-color:#7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,.1); }
.prf-pg .prf-pw-wrap { position:relative; }
.prf-pg .prf-pw-wrap .prf-inp { padding-right:40px; }
.prf-pg .prf-eye {
    position:absolute; right:12px; top:50%; transform:translateY(-50%);
    color:#94a3b8; font-size:.85rem; cursor:pointer; background:none; border:none; padding:2px;
    transition:color .2s;
}
.prf-pg .prf-eye:hover { color:#7c3aed; }
.prf-pg .prf-ferr { font-size:.72rem; color:#dc2626; margin-top:4px; display:flex; align-items:center; gap:4px; }
.prf-pg .prf-hint { font-size:.7rem; color:#94a3b8; margin-top:4px; }
.prf-pg .prf-g2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

/* foto upload */
.prf-pg .prf-foto-wrap {
    border:2px dashed #e2e8f0; border-radius:12px;
    padding:16px; text-align:center; cursor:pointer;
    background:#f8fafc; transition:border-color .2s, background .2s; position:relative;
}
.prf-pg .prf-foto-wrap:hover { border-color:#0ea5e9; background:#f0f9ff; }
.prf-pg .prf-foto-wrap input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; }
.prf-pg .prf-foto-ico { font-size:1.6rem; color:#bae6fd; margin-bottom:6px; }
.prf-pg .prf-foto-txt { font-size:.78rem; color:#0ea5e9; font-weight:700; }
.prf-pg .prf-foto-hint { font-size:.68rem; color:#94a3b8; margin-top:2px; }
.prf-pg #prfFotoPreview { display:none; width:72px; height:72px; border-radius:10px; object-fit:cover; margin:0 auto 8px; }

/* alerts */
.prf-pg .prf-ok, .prf-pg .prf-err {
    display:flex; align-items:center; gap:10px;
    padding:12px 14px; border-radius:12px; font-size:.84rem; margin:12px 16px 0;
}
.prf-pg .prf-ok  { background:#dcfce7; color:#15803d; border:1px solid #86efac; }
.prf-pg .prf-err { background:#fee2e2; color:#dc2626; border:1px solid #fecaca; }

/* action bars — z-index 100 agar tidak menutup sidebar */
.prf-pg .prf-bar {
    position:fixed; bottom:var(--footer-h,60px); left:0; right:0;
    padding:10px 16px 12px;
    background:rgba(255,255,255,.96); backdrop-filter:blur(10px);
    border-top:1px solid #e2e8f0; display:flex; gap:10px;
    z-index:100; box-shadow:0 -4px 20px rgba(0,0,0,.06);
}
.prf-pg .prf-sb {
    flex:1; display:inline-flex; align-items:center; justify-content:center;
    gap:7px; padding:12px 14px; border-radius:12px; font-size:.875rem;
    font-weight:700; border:none; cursor:pointer; font-family:inherit;
    transition:all .18s; line-height:1;
}
.prf-pg .prf-sb:active { transform:scale(.97); }
.prf-pg .prf-sb.blue {
    background:linear-gradient(135deg,#0369a1,#0ea5e9); color:#fff;
    box-shadow:0 3px 12px rgba(14,165,233,.3);
}
.prf-pg .prf-sb.blue:hover { filter:brightness(1.08); }
.prf-pg .prf-sb.purple {
    background:linear-gradient(135deg,#6d28d9,#7c3aed); color:#fff;
    box-shadow:0 3px 12px rgba(124,58,237,.3);
}
.prf-pg .prf-sb.purple:hover { filter:brightness(1.08); }
</style>
@endpush

@section('content')
<div class="prf-pg">

    {{-- Hero --}}
    <div class="prf-hero">
        <div class="prf-hero-nav">
            <div class="prf-live"><span class="prf-dot"></span>Profil Saya</div>
        </div>
    </div>

    {{-- Profile Card overlap --}}
    <div class="prf-profile-card">
        <div class="prf-ava">
            @if($user->avatar)
                <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}">
            @else
                <i class="fas fa-user"></i>
            @endif
            <div class="prf-ava-edit" onclick="document.getElementById('prfFotoInput').click()">
                <i class="fas fa-camera"></i>
            </div>
        </div>
        <div style="flex:1;min-width:0;">
            <h3 class="prf-uname">{{ $user->name }}</h3>
            <p class="prf-urole">{{ $user->email }}</p>
            <span class="prf-utag">
                <i class="fas fa-shield-alt"></i>
                {{ $user->getRoleNames()->first() ?? 'Pengguna' }}
            </span>
        </div>
    </div>

    <div class="prf-body">

        @if(session('success'))
            <div class="prf-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="prf-err"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
        @endif

        {{-- Form 1: Info Profil --}}
        <form id="prfInfoForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="prf-card">
                <div class="prf-chead">
                    <div class="prf-cico" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-user-edit"></i></div>
                    <h3>Informasi Profil</h3>
                </div>
                <div class="prf-cbody">

                    {{-- Nama --}}
                    <div class="prf-fg">
                        <label class="prf-lbl" for="prf_name">Nama Lengkap</label>
                        <input type="text" id="prf_name" name="name"
                            class="prf-inp @error('name') iserr @enderror"
                            value="{{ old('name', $user->name) }}" required maxlength="100">
                        @error('name')<div class="prf-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>

                    {{-- Email & Telepon --}}
                    <div class="prf-g2">
                        <div class="prf-fg">
                            <label class="prf-lbl" for="prf_email">Email</label>
                            <input type="email" id="prf_email" name="email"
                                class="prf-inp @error('email') iserr @enderror"
                                value="{{ old('email', $user->email) }}" required maxlength="100">
                            @error('email')<div class="prf-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                        </div>
                        <div class="prf-fg">
                            <label class="prf-lbl" for="prf_phone">No. Telepon</label>
                            <input type="tel" id="prf_phone" name="phone"
                                class="prf-inp @error('phone') iserr @enderror"
                                value="{{ old('phone', $user->phone) }}" maxlength="20"
                                placeholder="08xxxxxxxxxx">
                            @error('phone')<div class="prf-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="prf-divider"></div>

                    {{-- Foto --}}
                    <div class="prf-fg">
                        <label class="prf-lbl">Foto Profil</label>
                        <div class="prf-foto-wrap">
                            <input type="file" id="prfFotoInput" name="avatar" accept="image/*"
                                   onchange="prfPreview(this)">
                            <img id="prfFotoPreview" src="" alt="Preview">
                            <div class="prf-foto-ico" id="prfFotoIco"><i class="fas fa-cloud-upload-alt"></i></div>
                            <div class="prf-foto-txt" id="prfFotoTxt">Ketuk untuk ganti foto</div>
                            <div class="prf-foto-hint">JPG / PNG · Maks 2 MB</div>
                        </div>
                        @error('avatar')<div class="prf-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>

                </div>
            </div>

        </form>

        {{-- Form 2: Ubah Password --}}
        <form id="prfPwForm" action="{{ route('profile.change-password') }}" method="POST">
            @csrf

            <div class="prf-card">
                <div class="prf-chead">
                    <div class="prf-cico" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-key"></i></div>
                    <h3>Ubah Password</h3>
                </div>
                <div class="prf-cbody">

                    {{-- Password Saat Ini --}}
                    <div class="prf-fg">
                        <label class="prf-lbl" for="cur_pw">Password Saat Ini</label>
                        <div class="prf-pw-wrap">
                            <input type="password" id="cur_pw" name="current_password"
                                class="prf-inp prf-inp-pw @error('current_password') iserr @enderror"
                                placeholder="Masukkan password saat ini" required>
                            <button type="button" class="prf-eye" onclick="prfTogglePw('cur_pw','eye_cur')">
                                <i class="fas fa-eye" id="eye_cur"></i>
                            </button>
                        </div>
                        @error('current_password')<div class="prf-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>

                    {{-- Password Baru --}}
                    <div class="prf-g2">
                        <div class="prf-fg">
                            <label class="prf-lbl" for="new_pw">Password Baru</label>
                            <div class="prf-pw-wrap">
                                <input type="password" id="new_pw" name="password"
                                    class="prf-inp prf-inp-pw @error('password') iserr @enderror"
                                    placeholder="Password baru" required>
                                <button type="button" class="prf-eye" onclick="prfTogglePw('new_pw','eye_new')">
                                    <i class="fas fa-eye" id="eye_new"></i>
                                </button>
                            </div>
                            @error('password')<div class="prf-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                        </div>
                        <div class="prf-fg">
                            <label class="prf-lbl" for="conf_pw">Konfirmasi Password</label>
                            <div class="prf-pw-wrap">
                                <input type="password" id="conf_pw" name="password_confirmation"
                                    class="prf-inp prf-inp-pw @error('password_confirmation') iserr @enderror"
                                    placeholder="Ulangi password baru" required>
                                <button type="button" class="prf-eye" onclick="prfTogglePw('conf_pw','eye_conf')">
                                    <i class="fas fa-eye" id="eye_conf"></i>
                                </button>
                            </div>
                            @error('password_confirmation')<div class="prf-ferr"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                        </div>
                    </div>

                </div>
            </div>

        </form>

    </div>{{-- /prf-body --}}

    {{-- Action Bar: 2 tombol untuk 2 form berbeda --}}
    <div class="prf-bar">
        <button type="submit" form="prfInfoForm" class="prf-sb blue">
            <i class="fas fa-save"></i> Simpan Profil
        </button>
        <button type="submit" form="prfPwForm" class="prf-sb purple">
            <i class="fas fa-key"></i> Ubah Password
        </button>
    </div>

</div>{{-- /prf-pg --}}
@endsection

@push('scripts')
<script>
function prfPreview(input) {
    var prev = document.getElementById('prfFotoPreview');
    var ico  = document.getElementById('prfFotoIco');
    var txt  = document.getElementById('prfFotoTxt');
    if (input.files && input.files[0]) {
        var rd = new FileReader();
        rd.onload = function(e) {
            prev.src = e.target.result;
            prev.style.display = 'block';
            ico.style.display  = 'none';
            txt.textContent    = input.files[0].name;
        };
        rd.readAsDataURL(input.files[0]);
    }
}
function prfTogglePw(inputId, iconId) {
    var inp  = document.getElementById(inputId);
    var icon = document.getElementById(iconId);
    var hide = inp.type === 'password';
    inp.type    = hide ? 'text' : 'password';
    icon.className = hide ? 'fas fa-eye-slash' : 'fas fa-eye';
}

// Show SweetAlert for session messages
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

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: '{{ session('error') }}',
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Tutup',
    });
@endif
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush