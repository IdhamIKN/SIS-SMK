@extends('layouts.app')

@section('title', 'Manajemen Role & Permission')

@push('styles')
@include('components.izin-styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
/* ══════════════════════════════════════════
   BASE
══════════════════════════════════════════ */
.rp-pg {
    font-family: inherit;
    min-height: 100vh;
    background: #f1f5f9;
    padding-bottom: calc(var(--footer-h, 60px) + 80px);
}

/* ══════════════════════════════════════════
   HERO STRIP
══════════════════════════════════════════ */
.rp-strip {
    padding: calc(var(--header-h, 56px) + 24px) 20px 56px;
    background: linear-gradient(135deg, #1e1b4b 0%, #3730a3 55%, #6366f1 100%);
    position: relative;
    overflow: hidden;
}
.rp-strip::before {
    content: '';
    position: absolute;
    top: -60px; right: -40px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.rp-strip::after {
    content: '';
    position: absolute;
    bottom: -40px; left: -30px;
    width: 140px; height: 140px;
    background: rgba(255,255,255,.04);
    border-radius: 50%;
}
.rp-live {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    padding: 3px 12px;
    border-radius: 20px;
    font-size: .68rem;
    font-weight: 700;
    color: rgba(255,255,255,.9);
    margin-bottom: 12px;
    position: relative;
    z-index: 1;
}
.rp-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #a5b4fc;
    display: inline-block;
    animation: rpdot 2s infinite;
}
@keyframes rpdot { 0%,100%{opacity:1} 50%{opacity:.35} }

.rp-strip h2 {
    font-size: 1.35rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 6px;
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 10px;
}
.rp-strip p {
    font-size: .8rem;
    color: rgba(255,255,255,.65);
    margin: 0;
    position: relative;
    z-index: 1;
}

/* ══════════════════════════════════════════
   STAT CARDS  — mengambang di atas hero
══════════════════════════════════════════ */
.rp-stats-wrap {
    padding: 0 16px;
    margin-top: -34px;
    position: relative;
    z-index: 10;
    margin-bottom: 0;
}
.rp-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.rp-stat {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px 12px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,.10);
}
.rp-stat .s-ico {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .9rem;
    margin: 0 auto 8px;
}
.rp-stat .s-val {
    font-size: 1.6rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 4px;
}
.rp-stat .s-lbl {
    font-size: .63rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .05em;
}

/* ══════════════════════════════════════════
   ALERTS
══════════════════════════════════════════ */
.rp-alerts {
    padding: 20px 16px 0;
}
.rp-alerts .alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 12px;
    font-size: .82rem;
    margin-bottom: 8px;
}
.rp-alerts .a-ok  { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.rp-alerts .a-err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.rp-alerts .a-err ul { margin: 4px 0 0 14px; font-size: .78rem; }

/* ══════════════════════════════════════════
   MAIN BODY
══════════════════════════════════════════ */
.rp-body {
    padding: 24px 16px 0;
}

/* ── Section header ── */
.rp-sec-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}
.rp-sec-ico {
    width: 34px; height: 34px;
    border-radius: 10px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .85rem;
}
.rp-sec-head h3 {
    margin: 0;
    font-size: .8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #334155;
    white-space: nowrap;
}
.rp-sec-line {
    flex: 1;
    height: 1px;
    background: #e2e8f0;
    min-width: 12px;
}
.rp-sec-head .rp-btn { flex-shrink: 0; }

/* ══════════════════════════════════════════
   ROLE CARDS
══════════════════════════════════════════ */
.role-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
    transition: box-shadow .18s, transform .18s;
}
.role-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,.09);
    transform: translateY(-1px);
}
.role-card-top {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.role-avatar {
    width: 44px; height: 44px;
    border-radius: 13px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
}
.role-info {
    flex: 1;
    min-width: 0;
}
.role-title {
    font-size: .92rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 6px;
}
.role-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}
.rbadge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: .67rem;
    font-weight: 700;
}
.rb-blue   { background: #dbeafe; color: #1e40af; }
.rb-green  { background: #dcfce7; color: #166534; }
.rb-orange { background: #fff7ed; color: #c2410c; }

.role-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
    align-self: flex-start;
}

/* ── Permission tags dalam role card ── */
.perm-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
}
.ptag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 3px 9px;
    font-size: .67rem;
    font-weight: 600;
    color: #334155;
}
.ptag i { font-size: .58rem; color: #6366f1; }
.no-perm {
    font-size: .75rem;
    color: #94a3b8;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ══════════════════════════════════════════
   PERMISSION BLOCK
══════════════════════════════════════════ */
.perm-container {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}
.perm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 8px;
}
.perm-item {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 10px;
    transition: border-color .18s, background .18s;
}
.perm-item:hover { border-color: #6366f1; background: #eef2ff; }
.perm-item .perm-name {
    font-size: .78rem;
    font-weight: 600;
    color: #334155;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.perm-del-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #cbd5e1;
    padding: 0;
    line-height: 1;
    font-size: .8rem;
    flex-shrink: 0;
    transition: color .15s;
}
.perm-del-btn:hover { color: #dc2626; }

/* ══════════════════════════════════════════
   BUTTONS
══════════════════════════════════════════ */
.rp-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: .8rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    font-family: inherit;
    white-space: nowrap;
    transition: all .18s;
    text-decoration: none;
    line-height: 1;
}
.rp-btn:active { transform: scale(.95); }
.rp-btn-primary { background: #6366f1; color: #fff; box-shadow: 0 2px 8px rgba(99,102,241,.3); }
.rp-btn-primary:hover { background: #4f46e5; }
.rp-btn-green { background: #16a34a; color: #fff; box-shadow: 0 2px 8px rgba(22,163,74,.25); }
.rp-btn-green:hover { background: #15803d; }
.rp-btn-edit { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
.rp-btn-edit:hover { background: #fde68a; }
.rp-btn-del { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
.rp-btn-del:hover { background: #fecaca; }
.rp-btn-sm { padding: 6px 10px; font-size: .74rem; border-radius: 8px; }

/* ══════════════════════════════════════════
   EMPTY STATE
══════════════════════════════════════════ */
.rp-empty {
    text-align: center;
    padding: 32px 16px;
    color: #94a3b8;
}
.rp-empty i { font-size: 2.4rem; display: block; margin-bottom: 10px; opacity: .2; }
.rp-empty p { font-size: .82rem; margin: 0; }

/* ══════════════════════════════════════════
   MODAL
══════════════════════════════════════════ */
.rm-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,.6);
    z-index: 9998;
    align-items: center;
    justify-content: center;
    padding: 16px;
    backdrop-filter: blur(4px);
}
.rm-overlay.open { display: flex; }
.rm-box {
    background: #fff;
    border-radius: 20px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(0,0,0,.22);
    animation: rmIn .22s cubic-bezier(.34,1.56,.64,1);
}
@keyframes rmIn {
    from { opacity:0; transform:scale(.93) translateY(10px); }
    to   { opacity:1; transform:none; }
}
.rm-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px 16px;
    border-bottom: 1px solid #f1f5f9;
    flex-shrink: 0;
}
.rm-head h4 {
    margin: 0;
    font-size: .95rem;
    font-weight: 800;
    color: #0f172a;
}
.rm-close {
    background: none;
    border: none;
    cursor: pointer;
    color: #94a3b8;
    width: 32px; height: 32px;
    border-radius: 8px;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background .15s, color .15s;
}
.rm-close:hover { background: #f1f5f9; color: #475569; }

.rm-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}
.rm-foot {
    padding: 14px 20px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    flex-shrink: 0;
    background: #fafafa;
}

/* ── Form fields ── */
.rm-fg { margin-bottom: 16px; }
.rm-fg:last-child { margin-bottom: 0; }
.rm-fg label {
    display: block;
    font-size: .78rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 6px;
}
.rm-fg input[type="text"] {
    width: 100%;
    padding: 10px 13px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: .875rem;
    font-family: inherit;
    color: #0f172a;
    background: #f8fafc;
    outline: none;
    box-sizing: border-box;
    transition: border-color .2s, box-shadow .2s, background .2s;
}
.rm-fg input[type="text"]:focus {
    border-color: #6366f1;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
.rm-hint {
    font-size: .7rem;
    color: #94a3b8;
    margin-top: 5px;
    line-height: 1.5;
}
.rm-hint code {
    background: #f1f5f9;
    padding: 1px 5px;
    border-radius: 4px;
    font-size: .68rem;
}

/* ── Checklist permissions dalam modal ── */
.perm-cklist {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    max-height: 260px;
    overflow-y: auto;
    padding-right: 2px;
}
.perm-cklist::-webkit-scrollbar { width: 4px; }
.perm-cklist::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
.perm-cklist::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

.pck-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border: 1.5px solid #e2e8f0;
    border-radius: 9px;
    background: #f8fafc;
    cursor: pointer;
    font-size: .78rem;
    font-weight: 600;
    color: #334155;
    transition: all .15s;
    user-select: none;
}
.pck-item:has(input:checked) {
    border-color: #6366f1;
    background: #eef2ff;
    color: #3730a3;
}
.pck-item input[type="checkbox"] {
    width: 15px; height: 15px;
    accent-color: #6366f1;
    flex-shrink: 0;
    cursor: pointer;
    margin: 0;
}

/* ══════════════════════════════════════════
   RESPONSIVE — layar sangat sempit < 360px
══════════════════════════════════════════ */
@media (max-width: 359px) {
    .rp-stats { grid-template-columns: 1fr; }
    .perm-cklist { grid-template-columns: 1fr; }
    .perm-grid { grid-template-columns: 1fr; }
    .role-actions { flex-direction: column; }
}
</style>
@endpush

@section('content')
<div class="rp-pg">

    {{-- ── Hero Strip ── --}}
    <div class="rp-strip">
        <div class="rp-live"><span class="rp-dot"></span> Superadmin</div>
        <h2><i class="fas fa-user-shield"></i> Role & Permission</h2>
        <p>Kelola hak akses pengguna sistem secara terpusat</p>
    </div>

    {{-- ── Stat Cards (mengambang keluar dari hero) ── --}}
    <div class="rp-stats-wrap">
        <div class="rp-stats">
            <div class="rp-stat">
                <div class="s-ico" style="background:#eef2ff; color:#3730a3;">
                    <i class="fas fa-id-badge"></i>
                </div>
                <div class="s-val" style="color:#3730a3;">{{ $roles->count() }}</div>
                <div class="s-lbl">Total Role</div>
            </div>
            <div class="rp-stat">
                <div class="s-ico" style="background:#f0fdf4; color:#166534;">
                    <i class="fas fa-key"></i>
                </div>
                <div class="s-val" style="color:#166534;">{{ $permissions->count() }}</div>
                <div class="s-lbl">Total Permission</div>
            </div>
        </div>
    </div>

    {{-- ── Alerts ── --}}
    <div class="rp-alerts">
        @if(session('success'))
            <div class="alert a-ok">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="alert a-err">
                <i class="fas fa-exclamation-circle" style="flex-shrink:0;margin-top:1px;"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="alert a-err">
                <i class="fas fa-exclamation-circle" style="flex-shrink:0;margin-top:1px;"></i>
                <div>
                    <strong>Silakan perbaiki:</strong>
                    <ul>
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════
         SECTION: ROLES
    ══════════════════════════════════════ --}}
    <div class="rp-body">
        <div class="rp-sec-head">
            <span class="rp-sec-ico" style="background:#eef2ff; color:#3730a3;">
                <i class="fas fa-id-badge"></i>
            </span>
            <h3>Daftar Role</h3>
            <div class="rp-sec-line"></div>
            <button type="button" class="rp-btn rp-btn-primary" onclick="bukaModalRole()">
                <i class="fas fa-plus"></i> Tambah Role
            </button>
        </div>

        @forelse($roles as $role)
        @php
            $isSuperadmin = $role->name === 'superadmin';
            $avatarBg  = $isSuperadmin ? '#fef3c7' : '#eef2ff';
            $avatarClr = $isSuperadmin ? '#b45309' : '#3730a3';
            $icon      = $isSuperadmin ? 'fa-crown' : 'fa-id-badge';
        @endphp
        <div class="role-card">
            <div class="role-card-top">
                <div class="role-avatar" style="background:{{ $avatarBg }}; color:{{ $avatarClr }};">
                    <i class="fas {{ $icon }}"></i>
                </div>
                <div class="role-info">
                    <div class="role-title">{{ $role->name }}</div>
                    <div class="role-badges">
                        <span class="rbadge rb-blue">
                            <i class="fas fa-users"></i> {{ $role->users_count }} user
                        </span>
                        <span class="rbadge rb-green">
                            <i class="fas fa-key"></i> {{ $role->permissions_count }} permission
                        </span>
                        @if($isSuperadmin)
                            <span class="rbadge rb-orange">
                                <i class="fas fa-lock"></i> Protected
                            </span>
                        @endif
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════════════════════
                     FIX: data permissions disimpan di atribut data-* dengan
                     htmlspecialchars agar aman dari konflik quote dan karakter
                     khusus. JS membaca via btn.dataset.perms lalu JSON.parse
                     sekali — tidak ada double-parse seperti sebelumnya.
                ═══════════════════════════════════════════════════════════ --}}
                @if(!$isSuperadmin)
                <div class="role-actions">
                    <button type="button"
                        class="rp-btn rp-btn-edit rp-btn-sm"
                        data-id="{{ $role->id }}"
                        data-name="{{ $role->name }}"
                        
                        data-perms="{{ $role->permissions->pluck('name')->toJson() }}"
                        onclick="bukaModalEditRole(this)">
                        <i class="fas fa-pen"></i> Edit
                    </button>
                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="rp-btn rp-btn-del rp-btn-sm"
                            onclick="return confirm('Hapus role \'{{ $role->name }}\'?\nSemua user dengan role ini akan kehilangan akses.')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                @endif
            </div>

            {{-- Permission tags --}}
            @if($role->permissions->isNotEmpty())
            <div class="perm-tags">
                @foreach($role->permissions as $perm)
                <span class="ptag"><i class="fas fa-key"></i>{{ $perm->name }}</span>
                @endforeach
            </div>
            @else
            <p class="no-perm">
                <i class="fas fa-info-circle" style="opacity:.4;"></i>
                Belum ada permission yang ditetapkan
            </p>
            @endif
        </div>
        @empty
        <div class="rp-empty">
            <i class="fas fa-id-badge"></i>
            <p>Belum ada role. Tambahkan role baru.</p>
        </div>
        @endforelse
    </div>

    {{-- ══════════════════════════════════════
         SECTION: PERMISSIONS
    ══════════════════════════════════════ --}}
    <div class="rp-body" style="padding-bottom: 8px;">
        <div class="rp-sec-head">
            <span class="rp-sec-ico" style="background:#f0fdf4; color:#166534;">
                <i class="fas fa-key"></i>
            </span>
            <h3>Daftar Permission</h3>
            <div class="rp-sec-line"></div>
            <button type="button" class="rp-btn rp-btn-green" onclick="bukaModalPermission()">
                <i class="fas fa-plus"></i> Tambah Permission
            </button>
        </div>

        <div class="perm-container">
            @if($permissions->isNotEmpty())
            <div class="perm-grid">
                @foreach($permissions as $perm)
                <div class="perm-item">
                    <i class="fas fa-key" style="font-size:.65rem; color:#6366f1; flex-shrink:0;"></i>
                    <span class="perm-name">{{ $perm->name }}</span>
                    <form method="POST" action="{{ route('admin.permissions.destroy', $perm) }}" style="display:inline; flex-shrink:0;">
                        @csrf @method('DELETE')
                        <button type="submit" class="perm-del-btn"
                            title="Hapus permission ini"
                            onclick="return confirm('Hapus permission \'{{ $perm->name }}\'?')">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            @else
            <div class="rp-empty" style="padding: 24px 16px;">
                <i class="fas fa-key"></i>
                <p>Belum ada permission. Tambahkan permission baru.</p>
            </div>
            @endif
        </div>
    </div>

</div>{{-- end rp-pg --}}

{{-- ═══════════════════════════════════════════
     MODAL: Tambah / Edit Role
═══════════════════════════════════════════ --}}
<div class="rm-overlay" id="overlayRole">
    <div class="rm-box">
        <div class="rm-head">
            <h4 id="roleModalTitle">
                <i class="fas fa-id-badge" style="color:#6366f1; margin-right:6px;"></i>Tambah Role Baru
            </h4>
            <button class="rm-close" onclick="tutupModal('overlayRole')" aria-label="Tutup">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="roleForm" method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            <input type="hidden" name="_method" id="roleFormMethod" value="POST">
            <input type="hidden" name="_has_permissions" id="roleHasPerms" value="0">
            <div class="rm-body">
                <div class="rm-fg">
                    <label for="role_name">
                        Nama Role <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" id="role_name" name="name"
                        placeholder="Contoh: waka, bk, kurikulum"
                        maxlength="100" required autocomplete="off">
                    <div class="rm-hint">Nama role tidak boleh mengandung spasi. Gunakan underscore jika perlu.</div>
                </div>
                <div class="rm-fg" id="permGroupWrap" style="display:none;">
                    <label style="margin-bottom:8px; display:flex; align-items:center; justify-content:space-between;">
                        <span>Tetapkan Permissions</span>
                        <span id="permCheckedCount" style="font-size:.7rem; font-weight:600; color:#6366f1;"></span>
                    </label>
                    <div class="perm-cklist" id="permCheckGrid">
                        @foreach($permissions as $perm)
                        <label class="pck-item">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                onchange="updatePermCount()">
                            {{ $perm->name }}
                        </label>
                        @endforeach
                    </div>
                    @if($permissions->isEmpty())
                    <p style="font-size:.75rem; color:#94a3b8; margin: 8px 0 0;">
                        Belum ada permission. Tambahkan permission terlebih dahulu.
                    </p>
                    @endif
                </div>
            </div>
            <div class="rm-foot">
                <button type="button" class="rp-btn rp-btn-del" onclick="tutupModal('overlayRole')">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="submit" class="rp-btn rp-btn-primary" id="roleSubmitBtn">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     MODAL: Tambah Permission
═══════════════════════════════════════════ --}}
<div class="rm-overlay" id="overlayPermission">
    <div class="rm-box">
        <div class="rm-head">
            <h4>
                <i class="fas fa-key" style="color:#16a34a; margin-right:6px;"></i>Tambah Permission Baru
            </h4>
            <button class="rm-close" onclick="tutupModal('overlayPermission')" aria-label="Tutup">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.permissions.store') }}">
            @csrf
            <div class="rm-body">
                <div class="rm-fg">
                    <label for="perm_name">
                        Nama Permission <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" id="perm_name" name="name"
                        placeholder="Contoh: panel.realtime, absen.manage"
                        maxlength="150" required autocomplete="off">
                    <div class="rm-hint">
                        Gunakan format <code>modul.aksi</code> untuk konsistensi penamaan.
                    </div>
                </div>
            </div>
            <div class="rm-foot">
                <button type="button" class="rp-btn rp-btn-del" onclick="tutupModal('overlayPermission')">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="submit" class="rp-btn rp-btn-green">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Header active ── */
    var hdr = document.querySelector('.header-auto-show');
    if (hdr) hdr.classList.add('header-active');

    /* ── SweetAlert notifikasi ── */
    @if(session('success'))
        Swal.fire({
            icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}',
            confirmButtonColor: '#16a34a', timer: 3000, timerProgressBar: true,
            toast: true, position: 'top-end', showConfirmButton: false
        });
    @endif
    @if(session('error'))
        Swal.fire({
            icon: 'error', title: 'Gagal', text: '{{ session('error') }}',
            confirmButtonColor: '#dc2626'
        });
    @endif

    /* ════════════════════════════════════════
       MODAL HELPERS
    ════════════════════════════════════════ */
    window.tutupModal = function (id) {
        document.getElementById(id).classList.remove('open');
        document.body.style.overflow = '';
    };

    function bukaOverlay(id) {
        document.getElementById(id).classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    /* Klik backdrop → tutup */
    ['overlayRole', 'overlayPermission'].forEach(function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('click', function (e) {
            if (e.target === el) window.tutupModal(id);
        });
    });

    /* Escape key → tutup */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            ['overlayRole', 'overlayPermission'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el && el.classList.contains('open')) window.tutupModal(id);
            });
        }
    });

    /* ════════════════════════════════════════
       COUNTER CHECKBOX
    ════════════════════════════════════════ */
    window.updatePermCount = function () {
        var total   = document.querySelectorAll('#permCheckGrid input[type=checkbox]').length;
        var checked = document.querySelectorAll('#permCheckGrid input[type=checkbox]:checked').length;
        var el = document.getElementById('permCheckedCount');
        if (el && total > 0) {
            el.textContent = checked + ' / ' + total + ' dipilih';
        }
    };

    /* ════════════════════════════════════════
       MODAL TAMBAH ROLE
    ════════════════════════════════════════ */
    window.bukaModalRole = function () {
        document.getElementById('roleModalTitle').innerHTML =
            '<i class="fas fa-id-badge" style="color:#6366f1;margin-right:6px;"></i>Tambah Role Baru';
        document.getElementById('roleForm').action = '{{ route('admin.roles.store') }}';
        document.getElementById('roleFormMethod').value = 'POST';
        document.getElementById('role_name').value = '';
        document.getElementById('roleSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Simpan';

        /* Mode tambah: checklist tersembunyi, flag dimatikan */
        document.getElementById('permGroupWrap').style.display = 'none';
        document.getElementById('roleHasPerms').value = '0';
        document.querySelectorAll('#permCheckGrid input[type=checkbox]').forEach(function (cb) {
            cb.checked = false;
        });
        updatePermCount();

        bukaOverlay('overlayRole');
        setTimeout(function () { document.getElementById('role_name').focus(); }, 120);
    };

    /* ════════════════════════════════════════
       MODAL EDIT ROLE
       ─────────────────────────────────────
       FIX: Tidak lagi mengambil argumen perms
       sebagai string JS yang perlu di-parse
       ulang. Sekarang membaca dari atribut
       data-perms pada elemen tombol yang
       sudah di-escape oleh htmlspecialchars
       di sisi Blade, lalu JSON.parse sekali.
    ════════════════════════════════════════ */
    window.bukaModalEditRole = function (btn) {
        var id   = btn.dataset.id;
        var name = btn.dataset.name;
        var perms = [];

        try {
            perms = JSON.parse(btn.dataset.perms || '[]');
        } catch (e) {
            console.error('Gagal parse permissions:', e);
        }

        document.getElementById('roleModalTitle').innerHTML =
            '<i class="fas fa-pen" style="color:#b45309;margin-right:6px;"></i>'
            + 'Edit Role: <span style="color:#6366f1;">' + name + '</span>';

        var baseUrl = '{{ route('admin.roles.update', ['role' => '__ID__']) }}';
        document.getElementById('roleForm').action = baseUrl.replace('__ID__', id);
        document.getElementById('roleFormMethod').value = 'PUT';
        document.getElementById('role_name').value = name;
        document.getElementById('roleSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Perbarui';

        /* Mode edit: tampilkan checklist, aktifkan flag */
        document.getElementById('permGroupWrap').style.display = 'block';
        document.getElementById('roleHasPerms').value = '1';

        /* Centang permission yang sudah dimiliki role ini */
        document.querySelectorAll('#permCheckGrid input[type=checkbox]').forEach(function (cb) {
            cb.checked = perms.includes(cb.value);
        });
        updatePermCount();

        bukaOverlay('overlayRole');
        setTimeout(function () { document.getElementById('role_name').focus(); }, 120);
    };

    /* ════════════════════════════════════════
       MODAL TAMBAH PERMISSION
    ════════════════════════════════════════ */
    window.bukaModalPermission = function () {
        document.getElementById('perm_name').value = '';
        bukaOverlay('overlayPermission');
        setTimeout(function () { document.getElementById('perm_name').focus(); }, 120);
    };

});
</script>
@endpush