@extends('layouts.app')

@section('title', 'Buat Tahun Ajaran')

@push('styles')
@include('components.izin-styles')
<style>
/* ── Page strip ── */
.page-strip {
    padding: 20px 20px 28px; position: relative; overflow: hidden;
}
.page-strip-ta {
    background: linear-gradient(135deg, #10b981 0%, #059669 60%, #047857 100%);
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
.form-input:focus { border-color: #10b981; background: #fff; box-shadow: 0 0 0 3px rgba(16,185,129,.1); }
.form-input.is-error { border-color: #ef4444; }
.form-input[type="date"] {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center; padding-right: 40px;
}
.form-error { font-size: .72rem; color: #dc2626; margin-top: 4px; display: flex; align-items: center; gap: 4px; }
.form-hint  { font-size: .7rem; color: #94a3b8; margin-top: 4px; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

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
.ab-btn-primary { background: #10b981; color: #fff; box-shadow: 0 3px 12px rgba(16,185,129,.3); }
.ab-btn-primary:hover { filter: brightness(1.08); }

.ta-wrap { padding-bottom: calc(var(--footer-h) + 88px); }
</style>
@endpush

@section('content')
<div class="ta-wrap">

    {{-- Page Strip --}}
    <div class="page-strip page-strip-ta">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ now()->translatedFormat('d F Y') }}
        </div>
        <h2><i class="fas fa-plus-circle"></i> Buat Tahun Ajaran</h2>
        <p>Isi informasi tahun ajaran dengan lengkap</p>
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

    <form id="createAcademicYearForm" method="POST" action="{{ route('academic-years.store') }}">
        @csrf

        {{-- Info Tahun Ajaran --}}
        <div class="form-card">
            <div class="form-card-head">
                <div class="fc-icon" style="background:#dcfce7; color:#15803d;"><i class="fas fa-calendar-check"></i></div>
                <h3>Informasi Tahun Ajaran</h3>
            </div>
            <div class="form-card-body">

                {{-- Tahun Mulai --}}
                <div class="fgroup">
                    <label class="form-label" for="year_start">Tahun Mulai <span class="req">*</span></label>
                    <input type="number" id="year_start" name="year_start"
                        class="form-input @error('year_start') is-error @enderror"
                        value="{{ old('year_start', date('Y')) }}"
                        placeholder="2025" min="2020" max="2030" required>
                    <div class="form-hint">Tahun dimulainya ajaran (contoh: 2025)</div>
                    @error('year_start')<div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>

                {{-- Tahun Selesai --}}
                <div class="fgroup">
                    <label class="form-label" for="year_end">Tahun Selesai <span class="req">*</span></label>
                    <input type="number" id="year_end" name="year_end"
                        class="form-input @error('year_end') is-error @enderror"
                        value="{{ old('year_end', date('Y') + 1) }}"
                        placeholder="2026" min="2020" max="2031" required>
                    <div class="form-hint">Tahun berakhirnya ajaran (contoh: 2026)</div>
                    @error('year_end')<div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>

                {{-- Deadline Promosi --}}
                <div class="fgroup">
                    <label class="form-label" for="promotion_deadline">Deadline Promosi</label>
                    <input type="date" id="promotion_deadline" name="promotion_deadline"
                        class="form-input @error('promotion_deadline') is-error @enderror"
                        value="{{ old('promotion_deadline') }}"
                        min="{{ date('Y-m-d') }}">
                    <div class="form-hint">Tanggal terakhir untuk melakukan promosi siswa</div>
                    @error('promotion_deadline')<div class="form-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

        {{-- Preview --}}
        <div class="form-card">
            <div class="form-card-head">
                <div class="fc-icon" style="background:#ede9fe; color:#7c3aed;"><i class="fas fa-eye"></i></div>
                <h3>Preview</h3>
            </div>
            <div class="form-card-body">
                <div style="padding: 16px; background: #f8fafc; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.2rem; font-weight: 700; color: #0f172a; margin-bottom: 4px;">
                        <span id="preview-name">2025/2026</span>
                    </div>
                    <div style="font-size: .8rem; color: #64748b;">
                        Tahun Ajaran <span id="preview-years">2025 - 2026</span>
                    </div>
                    <div style="font-size: .7rem; color: #94a3b8; margin-top: 8px;">
                        Status: <strong>Belum Aktif</strong>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>

{{-- Action Bar --}}
<div class="action-bar">
    <a href="{{ route('academic-years.index') }}" class="ab-btn ab-btn-back">
        <i class="fas fa-times"></i> Batal
    </a>
    <button type="submit" form="createAcademicYearForm" class="ab-btn ab-btn-primary">
        <i class="fas fa-save"></i> Simpan Tahun Ajaran
    </button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const yearStartInput = document.getElementById('year_start');
    const yearEndInput = document.getElementById('year_end');
    const previewName = document.getElementById('preview-name');
    const previewYears = document.getElementById('preview-years');

    function updatePreview() {
        const start = yearStartInput.value;
        const end = yearEndInput.value;
        if (start && end) {
            previewName.textContent = start + '/' + end;
            previewYears.textContent = start + ' - ' + end;
        }
    }

    yearStartInput.addEventListener('input', function() {
        if (this.value) {
            yearEndInput.value = parseInt(this.value) + 1;
        }
        updatePreview();
    });

    yearEndInput.addEventListener('input', updatePreview);

    // Initial preview
    updatePreview();
});
</script>
@endpush