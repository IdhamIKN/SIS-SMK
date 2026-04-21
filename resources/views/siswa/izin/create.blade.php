@extends('layouts.app')

@section('title', 'Ajukan Izin Baru')

@push('styles')
    @include('components.izin-styles')
@endpush

@section('content')
    <div class="izin-wrap">
        {{-- Page Strip --}}
        <div class="page-strip page-strip-izin">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <h2>
                <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Ajukan Izin Baru
            </h2>
            <p>Isi form dengan lengkap & jelas untuk proses cepat</p>
        </div>

        {{-- Alerts --}}
        @if ($errors->any())
            <div class="alert a-warn">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Silakan perbaiki:</strong>
                    <ul style="margin: 4px 0 0 16px; font-size: .85rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Steps --}}
        <div class="steps">
            <div class="step active">
                <div class="step-dot">1</div>
                <div class="step-lbl">Jenis Izin</div>
            </div>
            <div class="step">
                <div class="step-dot">2</div>
                <div class="step-lbl">Tanggal</div>
            </div>
            <div class="step">
                <div class="step-dot">3</div>
                <div class="step-lbl">Alasan</div>
            </div>
            <div class="step">
                <div class="step-dot">4</div>
                <div class="step-lbl">Kirim</div>
            </div>
        </div>

        {{-- Status Chips --}}
        <div class="status-bar">
            <div class="s-chip">
                <div class="ci ci-p" id="jenisIcon">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <div class="c-lbl">Jenis</div>
                    <div class="c-val" id="jenisVal">Pilih jenis</div>
                </div>
            </div>
            <div class="s-chip">
                <div class="ci" id="tanggalIcon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div>
                    <div class="c-lbl">Tanggal</div>
                    <div class="c-val" id="tanggalVal">Pilih tanggal</div>
                </div>
            </div>
        </div>

        {{-- Form Card --}}
        <form id="izinForm" method="POST" action="{{ route('siswa.izin.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background: var(--purple-fade);">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>Detail Pengajuan</h3>
                    <span class="hbadge">Wajib lengkap</span>
                </div>
                <div class="c-body" style="padding: 20px;">

                    {{-- Jenis --}}
                    <div class="mb-4">
                        <label style="font-weight: 600; color: var(--text-main); margin-bottom: 8px; display: block;">
                            Jenis Izin <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="jenis" id="jenisSelect" class="form-select"
                            style="border: 2px solid var(--border); border-radius: 12px; padding: 12px 16px; font-size: .95rem; transition: border-color .2s; background: var(--card);"
                            required>
                            <option value="">Pilih jenis izin</option>
                            <option value="izin_sakit" {{ old('jenis') == 'izin_sakit' ? 'selected' : '' }}>Izin Sakit
                            </option>
                            <option value="izin_pulang_cepat" {{ old('jenis') == 'izin_pulang_cepat' ? 'selected' : '' }}>
                                Izin Pulang Cepat</option>
                            <option value="izin_terlambat" {{ old('jenis') == 'izin_terlambat' ? 'selected' : '' }}>Izin
                                Terlambat</option>
                            <option value="izin_lainnya" {{ old('jenis') == 'izin_lainnya' ? 'selected' : '' }}>Izin Lainnya
                            </option>
                        </select>
                    </div>

                    {{-- Date Section --}}
                    <div class="date-section mb-4" id="dateSection">
                        {{-- Single Date for pulang_cepat/terlambat --}}
                        <div id="singleDate" class="date-group">
                            <label style="font-weight: 600; color: var(--text-main); margin-bottom: 8px; display: block;">
                                Tanggal Izin <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="date" name="tanggal_izin" id="tanggalInput" class="form-control"
                                value="{{ old('tanggal_izin') }}" min="{{ now()->format('Y-m-d') }}"
                                style="border: 2px solid var(--border); border-radius: 12px; padding: 12px 16px; font-size: .95rem; transition: border-color .2s; background: var(--card);">
                        </div>

                        {{-- Range Date for sakit/lainnya --}}
                        <div id="rangeDate" class="date-group" style="display: none;">
                            <label style="font-weight: 600; color: var(--text-main); margin-bottom: 12px; display: block;">
                                Rentang Tanggal Izin <span style="color: #ef4444;">*</span>
                            </label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div>
                                    <label
                                        style="font-size: .85rem; color: var(--text-muted); display: block; margin-bottom: 4px;">Mulai</label>
                                    <input type="date" name="tanggal_mulai" id="tanggalMulaiInput" class="form-control"
                                        value="{{ old('tanggal_mulai') }}" min="{{ now()->format('Y-m-d') }}"
                                        style="border: 2px solid var(--border); border-radius: 12px; padding: 12px 16px; font-size: .95rem; transition: border-color .2s; background: var(--card);">
                                </div>
                                <div>
                                    <label
                                        style="font-size: .85rem; color: var(--text-muted); display: block; margin-bottom: 4px;">Sampai</label>
                                    <input type="date" name="tanggal_sampai" id="tanggalSampaiInput" class="form-control"
                                        value="{{ old('tanggal_sampai') }}" min="{{ now()->format('Y-m-d') }}"
                                        style="border: 2px solid var(--border); border-radius: 12px; padding: 12px 16px; font-size: .95rem; transition: border-color .2s; background: var(--card);">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Alasan --}}
                    <div style="margin-bottom: 24px;">
                        <label style="font-weight: 600; color: var(--text-main); margin-bottom: 8px; display: block;">
                            Alasan <span style="color: #ef4444;">*</span>
                        </label>
                        <textarea name="alasan" id="alasanInput" rows="5" class="form-control"
                            placeholder="Jelaskan alasan izin secara detail. Pengajuan akan diverifikasi oleh admin/BK."
                            style="border: 2px solid var(--border); border-radius: 12px; padding: 16px; font-size: .9rem; line-height: 1.5; resize: vertical; transition: border-color .2s; background: var(--card);"
                            required>{{ old('alasan') }}</textarea>
                        <div style="font-size: .75rem; color: var(--text-muted); margin-top: 6px;">
                            Maksimal 500 karakter. Semakin detail semakin baik prosesnya.
                        </div>
                    </div>

                    {{-- Bukti Upload --}}
                    <div class="mb-4 bukti-section" id="buktiSection">
                        <label style="font-weight: 600; color: var(--text-main); margin-bottom: 8px; display: block;"
                            id="buktiLabel">
                            Bukti/Surat Izin <span style="color: #ef4444;" id="buktiRequired">*</span>
                        </label>
                        <div style="position: relative; border: 2px dashed var(--border); border-radius: 12px; padding: 32px 16px; text-align: center; transition: all .2s; cursor: pointer;"
                            id="fileDropZone">
                            <input type="file" name="bukti" id="buktiInput" accept="image/jpeg,image/jpg,image/png"
                                class="d-none" data-max-size="10240">
                            <i class="fas fa-cloud-upload-alt"
                                style="font-size: 2.5rem; color: var(--purple-primary); margin-bottom: 12px; display: block;"></i>
                            <div style="font-size: .9rem; color: var(--text-main); margin-bottom: 4px;">Klik atau drag
                                gambar bukti (JPG/PNG)</div>
                            <div style="font-size: .75rem; color: var(--text-muted);">Maks 10MB - Akan dikompres otomatis
                            </div>
                            <div id="filePreview" style="margin-top: 12px; display: none;">
                                <img id="previewImg"
                                    style="max-width: 100%; max-height: 200px; border-radius: 8px; border: 1px solid var(--border);">
                                <div id="fileSize" style="font-size: .75rem; color: var(--text-muted); margin-top: 4px;">
                                </div>
                                <button type="button" id="removeFile" class="btn-izin btn-izin-secondary mt-2"
                                    style="font-size: .75rem;">Hapus</button>
                            </div>
                        </div>
                        <div style="font-size: .7rem; color: var(--text-muted); margin-top: 8px;">
                            Wajib untuk sakit/terlambat/lainnya. Opsional untuk pulang cepat.
                        </div>
                    </div>

                    <button type="submit" class="btn-sub" id="submitBtn">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Ajukan Izin Sekarang
                    </button>

                </div>
            </div>
        </form>

        {{-- Back Link --}}
        <a href="{{ route('siswa.izin.index') }}" class="btn-izin btn-izin-secondary"
            style="position: fixed; top: 24px; left: 24px; z-index: 1000;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Riwayat
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const jenisSelect = document.getElementById('jenisSelect');
            const tanggalInput = document.getElementById('tanggalInput');
            const alasanInput = document.getElementById('alasanInput');
            const jenisVal = document.getElementById('jenisVal');
            const tanggalVal = document.getElementById('tanggalVal');
            const jenisIcon = document.getElementById('jenisIcon');
            const tanggalIcon = document.getElementById('tanggalIcon');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('izinForm');

            const jenisLabels = {
                'izin_sakit': 'Sakit',
                'izin_pulang_cepat': 'Pulang Cepat',
                'izin_terlambat': 'Terlambat',
                'izin_lainnya': 'Lainnya'
            };

            function handleJenisChange() {
                const jenis = jenisSelect.value;
                const singleDate = document.getElementById('singleDate');
                const rangeDate = document.getElementById('rangeDate');

                if (jenis === 'izin_sakit' || jenis === 'izin_lainnya') {
                    singleDate.style.display = 'none';
                    rangeDate.style.display = 'block';
                    tanggalIcon.innerHTML = '<i class="fas fa-calendar-range"></i>';
                    tanggalVal.textContent = 'Pilih rentang';
                } else if (jenis === 'izin_pulang_cepat' || jenis === 'izin_terlambat') {
                    singleDate.style.display = 'block';
                    rangeDate.style.display = 'none';
                    tanggalIcon.innerHTML = '<i class="fas fa-calendar"></i>';
                    tanggalVal.textContent = 'Pilih tanggal';
                } else {
                    singleDate.style.display = 'block';
                    rangeDate.style.display = 'none';
                    tanggalVal.textContent = 'Pilih tanggal';
                }
                updateChips();
            }

            function updateChips() {
                // Jenis
                const jenis = jenisSelect.value;
                if (jenis) {
                    jenisVal.textContent = jenisLabels[jenis];
                    jenisIcon.innerHTML = '<i class="fas fa-check"></i>';
                    jenisIcon.classList.add('ci-p');
                } else {
                    jenisVal.textContent = 'Pilih jenis';
                    jenisIcon.innerHTML = '<i class="fas fa-list"></i>';
                    jenisIcon.classList.remove('ci-p');
                }

                // Tanggal validation
                let hasValidDate = false;
                if (jenisSelect.value === 'izin_sakit' || jenisSelect.value === 'izin_lainnya') {
                    const mulai = document.getElementById('tanggalMulaiInput').value;
                    const sampai = document.getElementById('tanggalSampaiInput').value;
                    hasValidDate = mulai && sampai && new Date(sampai) >= new Date(mulai);
                    if (hasValidDate) {
                        const dateMulai = new Date(mulai);
                        tanggalVal.textContent = dateMulai.toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric'
                        }) + ' - ' + new Date(sampai).toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short'
                        });
                        tanggalIcon.innerHTML = '<i class="fas fa-check"></i>';
                        tanggalIcon.classList.add('ci-p');
                    } else {
                        tanggalVal.textContent = 'Pilih rentang';
                        tanggalIcon.innerHTML = '<i class="fas fa-calendar-range"></i>';
                        tanggalIcon.classList.remove('ci-p');
                    }
                } else {
                    const tanggal = tanggalInput.value;
                    hasValidDate = tanggal;
                    if (tanggal) {
                        const date = new Date(tanggal);
                        tanggalVal.textContent = date.toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric'
                        });
                        tanggalIcon.innerHTML = '<i class="fas fa-check"></i>';
                        tanggalIcon.classList.add('ci-p');
                    } else {
                        tanggalVal.textContent = 'Pilih tanggal';
                        tanggalIcon.innerHTML = '<i class="fas fa-calendar"></i>';
                        tanggalIcon.classList.remove('ci-p');
                    }
                }

                // Enable submit if all filled
                const ready = jenis && hasValidDate && alasanInput.value.trim().length > 10;
                submitBtn.disabled = !ready;
                submitBtn.style.background = ready ? 'var(--purple-dark)' : 'var(--purple-primary)';
            }

            // Events
            jenisSelect.addEventListener('change', handleJenisChange);
            document.getElementById('tanggalInput').addEventListener('change', updateChips);
            document.getElementById('tanggalMulaiInput').addEventListener('change', updateChips);
            document.getElementById('tanggalSampaiInput').addEventListener('change', updateChips);
            alasanInput.addEventListener('input', updateChips);

            // Bukti upload
            const buktiInput = document.getElementById('buktiInput');
            const fileDropZone = document.getElementById('fileDropZone');
            const buktiLabel = document.getElementById('buktiLabel');
            const buktiRequired = document.getElementById('buktiRequired');
            const previewImg = document.getElementById('previewImg');
            const filePreview = document.getElementById('filePreview');
            const fileSize = document.getElementById('fileSize');
            const removeFile = document.getElementById('removeFile');

            function updateBuktiRequired() {
                const jenis = jenisSelect.value;
                const isPulangCepat = jenis === 'izin_pulang_cepat';
                buktiRequired.style.display = isPulangCepat ? 'none' : 'inline';
                buktiLabel.title = isPulangCepat ? 'Opsional untuk pulang cepat' : 'Wajib untuk jenis ini';
            }

            // File input
            buktiInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 10240 * 1024) {
                        alert('File terlalu besar! Maksimal 10MB.');
                        e.target.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        fileSize.textContent = (file.size / 1024 / 1024).toFixed(1) + ' MB';
                        filePreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Drag drop
            fileDropZone.addEventListener('click', () => buktiInput.click());
            fileDropZone.addEventListener('dragover', e => e.preventDataTransfer = false);
            fileDropZone.addEventListener('drop', e => {
                e.preventDefault();
                const file = e.dataTransfer.files[0];
                if (file) buktiInput.files = e.dataTransfer.files;
                buktiInput.dispatchEvent(new Event('change'));
            });

            removeFile.addEventListener('click', () => {
                buktiInput.value = '';
                filePreview.style.display = 'none';
            });

            jenisSelect.addEventListener('change', updateBuktiRequired);

            updateBuktiRequired();

            // Initial
            handleJenisChange();
            updateChips();

            // Form submit
            form.addEventListener('submit', function(e) {
                if (submitBtn.disabled) {
                    e.preventDefault();
                    alert('Lengkapi semua field terlebih dahulu!');
                }
            });
        });
    </script>
@endsection
