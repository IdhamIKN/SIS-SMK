@extends('layouts.app')

@section('title', 'Import Pasal')

@push('styles')
    @include('components.event-styles')
    @include('admin.tatib._styles')
@endpush

@section('content')
<div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">
    <div class="page-strip page-strip-event">
        <div class="live-badge"><span class="live-dot"></span>{{ now()->translatedFormat('l, d F Y') }}</div>
        <h2><i class="fas fa-file-import"></i> Import Pasal</h2>
        <p>Master pasal tata tertib</p>
    </div>

    @if (session('error'))
        <div class="alert a-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.pasal.import.process') }}" enctype="multipart/form-data">
        @csrf

        <div class="tatib-form-card">
            <div class="tatib-form-grid">
                <div class="tatib-field">
                    <label>Jenis Default</label>
                    <select name="jenis" class="tatib-select">
                        <option value="pelanggaran" @selected(old('jenis', $jenis) === 'pelanggaran')>Pelanggaran</option>
                        <option value="penghargaan" @selected(old('jenis', $jenis) === 'penghargaan')>Penghargaan</option>
                    </select>
                    @error('jenis') <span class="tatib-error">{{ $message }}</span> @enderror
                </div>

                <div class="tatib-field">
                    <label>Tahun Ajaran Default</label>
                    <input type="text" name="tahun_ajaran" class="tatib-input"
                        value="{{ old('tahun_ajaran', $tahunAjaran) }}" maxlength="9" required>
                    @error('tahun_ajaran') <span class="tatib-error">{{ $message }}</span> @enderror
                </div>

                <div class="tatib-field full">
                    <label>File Excel/CSV</label>
                    <input type="file" name="file" class="tatib-input" accept=".xlsx,.xls,.csv,.txt" required>
                    @error('file') <span class="tatib-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="tatib-card" style="margin-bottom:12px;">
            <div>
                <h3 class="tatib-title">Format Kolom</h3>
                <p class="tatib-body">
                    Gunakan header: jenis, idkategori, idpasal, pasal, skormin, skormax, urut, tahun_ajaran, status_aktif.
                    Kolom jenis dan tahun_ajaran boleh kosong jika memakai nilai default di atas.
                </p>
            </div>
            <div class="tatib-actions">
                <a href="{{ route('admin.pasal.template') }}" class="tatib-btn tatib-btn-soft">
                    <i class="fas fa-download"></i> Template
                </a>
            </div>
        </div>

        <div class="tatib-actions">
            <a href="{{ route('admin.pasal.index', ['jenis' => $jenis, 'tahun_ajaran' => $tahunAjaran]) }}"
                class="tatib-btn tatib-btn-soft"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="tatib-btn tatib-btn-primary"><i class="fas fa-upload"></i> Import</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('.header-auto-show');
    if (header) header.classList.add('header-active');
});
</script>
@endpush
