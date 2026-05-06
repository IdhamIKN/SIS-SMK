@extends('layouts.app')

@section('title', 'Promosi Siswa - ' . $kela->nama_kelas)

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

        /* ── Student list ── */
        .student-list { margin: 0 16px 12px; }
        .student-item {
            background: #fff; border: 1px solid var(--border, #e2e8f0); border-radius: 16px;
            margin-bottom: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.04); overflow: hidden;
            display: flex; align-items: center; gap: 12px; padding: 12px 16px;
        }
        .student-checkbox {
            width: 20px; height: 20px;
            accent-color: #7c3aed;
        }
        .student-ava {
            width: 40px; height: 40px; border-radius: 10px;
            background: #ede9fe; color: #7c3aed;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0; overflow: hidden;
        }
        .student-ava img { width: 100%; height: 100%; object-fit: cover; }
        .student-info { flex: 1; min-width: 0; }
        .student-name {
            font-size: .85rem; font-weight: 700; color: #0f172a;
            margin: 0 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .student-detail { font-size: .7rem; color: #64748b; margin: 0; }

        /* ── Select all ── */
        .select-all {
            background: #f8fafc; border: 1px solid var(--border, #e2e8f0); border-radius: 12px;
            margin: 12px 16px; padding: 12px 16px;
            display: flex; align-items: center; gap: 12px;
        }
        .select-all-checkbox {
            width: 20px; height: 20px;
            accent-color: #7c3aed;
        }
        .select-all-text {
            font-size: .85rem; font-weight: 600; color: #0f172a;
            flex: 1;
        }

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
        .ab-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        .kelas-wrap { padding-bottom: calc(var(--footer-h) + 88px); }
        .empty-state { text-align: center; padding: 40px 20px; color: #94a3b8; font-size: .82rem; }
        .empty-state i { display: block; font-size: 1.8rem; margin-bottom: 6px; opacity: .4; }
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
        <h2><i class="fas fa-arrow-up"></i> Promosi Siswa</h2>
        <p>Kelas {{ $kela->nama_kelas }} → {{ $nextTingkat ? $nextTingkat : 'Lulus' }}</p>
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

    @if(isset($error))
        <div class="alert a-err">
            <i class="fas fa-exclamation-triangle"></i>
            {{ $error }}
        </div>
    @endif

    <form id="promotionForm" method="POST" action="{{ route('kelas.execute-promotion', $kela) }}">
        @csrf

        {{-- Target Class Selection --}}
        <div class="form-card">
            <div class="form-card-head">
                <div class="fc-icon" style="background:#ede9fe; color:#7c3aed;"><i class="fas fa-arrow-right"></i></div>
                <h3>Pilih Kelas Tujuan</h3>
            </div>
            <div class="form-card-body">
                <div class="fgroup">
                    <label class="form-label" for="target_class_id">Kelas Tujuan <span class="req">*</span></label>
                    <select id="target_class_id" name="target_class_id" class="form-input" required>
                        <option value="">— Pilih kelas tujuan —</option>
                        @foreach ($targetKelas as $target)
                            <option value="{{ $target->id }}">{{ $target->nama_kelas }}</option>
                        @endforeach
                    </select>
                    <div class="form-hint">
                        @if($nextTingkat)
                            Siswa akan dipindahkan ke tingkat {{ $nextTingkat }}
                        @else
                            Kelas ini sudah tingkat tertinggi (XII)
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Select All --}}
        @if($siswa->count() > 0)
        <div class="select-all">
            <input type="checkbox" id="selectAll" class="select-all-checkbox">
            <label for="selectAll" class="select-all-text">Pilih Semua Siswa ({{ $siswa->count() }} siswa)</label>
        </div>
        @endif

        {{-- Student List --}}
        @if ($siswa->count() > 0)
            <div class="student-list">
                @foreach ($siswa as $s)
                    <div class="student-item">
                        <input type="checkbox" name="student_ids[]" value="{{ $s->id }}"
                               class="student-checkbox student-checkbox-item"
                               id="student_{{ $s->id }}">
                        <label for="student_{{ $s->id }}" style="display: flex; align-items: center; gap: 12px; flex: 1; cursor: pointer;">
                            <div class="student-ava">
                                @if ($s->foto)
                                    <img src="{{ Storage::url($s->foto) }}" alt="{{ $s->nama_lengkap }}">
                                @else
                                    <i class="fas fa-user-graduate"></i>
                                @endif
                            </div>
                            <div class="student-info">
                                <h4 class="student-name">{{ $s->nama_lengkap }}</h4>
                                <p class="student-detail">
                                    NIS: {{ $s->nis ?? '-' }}{{ $s->nisn ? ' / ' . $s->nisn : '' }}
                                </p>
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>Tidak ada siswa di kelas ini.</p>
            </div>
        @endif

    </form>
</div>

{{-- Action Bar --}}
<div class="action-bar">
    <a href="{{ route('kelas.show', $kela) }}" class="ab-btn ab-btn-back">
        <i class="fas fa-times"></i> Batal
    </a>
    <button type="submit" form="promotionForm" class="ab-btn ab-btn-primary" id="submitBtn" disabled>
        <i class="fas fa-arrow-up"></i> Promosikan Siswa
    </button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox-item');
    const submitBtn = document.getElementById('submitBtn');
    const targetClassSelect = document.getElementById('target_class_id');

    // Select All functionality
    selectAllCheckbox.addEventListener('change', function() {
        studentCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSubmitButton();
    });

    // Individual checkbox change
    studentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('.student-checkbox-item:checked').length;
            selectAllCheckbox.checked = checkedCount === studentCheckboxes.length && studentCheckboxes.length > 0;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < studentCheckboxes.length;
            updateSubmitButton();
        });
    });

    // Target class selection change
    targetClassSelect.addEventListener('change', updateSubmitButton);

    function updateSubmitButton() {
        const hasSelectedStudents = document.querySelectorAll('.student-checkbox-item:checked').length > 0;
        const hasTargetClass = targetClassSelect.value !== '';
        submitBtn.disabled = !hasSelectedStudents || !hasTargetClass;

        const selectedCount = document.querySelectorAll('.student-checkbox-item:checked').length;
        const btnText = selectedCount > 0
            ? `Promosikan ${selectedCount} Siswa`
            : 'Promosikan Siswa';
        submitBtn.innerHTML = `<i class="fas fa-arrow-up"></i> ${btnText}`;
    }

    // Initial state
    updateSubmitButton();
});
</script>
@endpush