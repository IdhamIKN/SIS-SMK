@php
    $isEdit = isset($jurnal);
    $canSelectGtk = $canSelectGtk ?? false;
    $selectedJadwalId = (string) old('jadwal_kbm_id', $selectedJadwalId ?? '');
    $selectedGtkId = (string) old('gtk_id', $selectedGtkId ?? $gtk?->id);
    $selectedSiswaIds = collect(old('namasiswa', $selectedSiswaIds ?? []))
        ->map(fn($id) => (string) $id)
        ->all();
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="jurnal-form">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#e0f2fe;"><i class="fas fa-calendar-check"></i></div>
            <h3>Jadwal Pembelajaran</h3>
        </div>
        <div class="c-body form-grid">
            <div class="form-group">
                <label class="form-label" for="tanggal">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" class="form-input"
                    value="{{ old('tanggal', $tanggal) }}" required>
                @error('tanggal')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            @if ($canSelectGtk)
                <div class="form-group">
                    <label class="form-label" for="gtk_id">Guru</label>
                    <select id="gtk_id" name="gtk_id" class="form-input" required>
                        <option value="">Pilih guru</option>
                        @foreach ($gtkList as $guru)
                            <option value="{{ $guru->id }}"
                                {{ $selectedGtkId === (string) $guru->id ? 'selected' : '' }}>
                                {{ $guru->nama_lengkap }} ({{ $guru->kd_guru }})
                            </option>
                        @endforeach
                    </select>
                    @error('gtk_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            @else
                <div class="form-group">
                    <label class="form-label">Guru</label>
                    <input type="text" class="form-input" value="{{ $gtk->nama_lengkap }} ({{ $gtk->kd_guru }})"
                        readonly>
                </div>
            @endif

            <div class="form-group">
                <label class="form-label" for="jadwal_kbm_id">Jadwal KBM</label>
                <select id="jadwal_kbm_id" name="jadwal_kbm_id" class="form-input" required>
                    <option value="">Pilih jadwal</option>
                    @foreach ($jadwalOptions as $jadwal)
                        <option value="{{ $jadwal['id'] }}"
                            {{ $selectedJadwalId === (string) $jadwal['id'] ? 'selected' : '' }}
                            {{ $jadwal['jurnal_id'] ? 'disabled' : '' }}>
                            Jam {{ $jadwal['jam_ke'] }} - {{ $jadwal['kelas'] }} - {{ $jadwal['pelajaran'] }}
                            ({{ $jadwal['jam_mulai'] }}-{{ $jadwal['jam_selesai'] }})
                            {{ $jadwal['jurnal_id'] ? ' - sudah ada jurnal' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('jadwal_kbm_id')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                @if (empty($jadwalOptions))
                    <div class="form-hint">
                        {{ $canSelectGtk && !$gtk ? 'Pilih guru dan tanggal terlebih dahulu.' : 'Tidak ada jadwal mengajar untuk tanggal ini.' }}
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label class="form-label">Tipe</label>
                <input type="text" class="form-input" value="Luring" readonly>
            </div>
        </div>

        <div class="schedule-summary" id="scheduleSummary">
            <div>
                <span class="summary-label">Kelas</span>
                <strong id="summaryKelas">-</strong>
            </div>
            <div>
                <span class="summary-label">Mata Pelajaran</span>
                <strong id="summaryPelajaran">-</strong>
            </div>
            <div>
                <span class="summary-label">Waktu</span>
                <strong id="summaryWaktu">-</strong>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#dcfce7;"><i class="fas fa-users"></i></div>
            <h3>Kehadiran Siswa</h3>
        </div>
        <div class="c-body">
            <div class="counter-grid">
                <div class="counter-box">
                    <span>Total Siswa</span>
                    <strong id="countTotal">0</strong>
                </div>
                <div class="counter-box">
                    <span>Hadir</span>
                    <strong id="countHadir">0</strong>
                </div>
                <div class="counter-box danger">
                    <span>Tidak Hadir</span>
                    <strong id="countTidakHadir">0</strong>
                </div>
            </div>

            <div class="form-group mb-0">
                <label class="form-label" for="namasiswa">Siswa Tidak Hadir</label>
                <select id="namasiswa" name="namasiswa[]" class="form-input" multiple size="8"></select>
                <div class="form-hint">Tahan Ctrl untuk memilih lebih dari satu siswa di desktop. Di ponsel, pilih satu
                    per satu dari daftar.</div>
                @error('namasiswa')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                @error('namasiswa.*')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#fef3c7;"><i class="fas fa-book-open"></i></div>
            <h3>Materi dan Bukti</h3>
        </div>
        <div class="c-body">
            <div class="form-group">
                <label class="form-label" for="deskripsi">Deskripsi Pembelajaran</label>
                <textarea id="deskripsi" name="deskripsi" class="form-input" rows="5" required>{{ old('deskripsi', $jurnal->deskripsi ?? '') }}</textarea>
                @error('deskripsi')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="upload-grid">
                <label class="upload-choice">
                    <i class="fas fa-camera"></i>
                    <span>Kamera langsung <small style="opacity:.6"></small></span>
                    <input type="file" name="bukti_kamera" id="bukti_kamera" accept="image/*" capture="environment">
                </label>

                <label class="upload-choice">
                    <i class="fas fa-file-upload"></i>
                    <span>Pilih file <small style="opacity:.6"></small></span>
                    <input type="file" name="bukti_file" id="bukti_file" accept="image/*,application/pdf">
                </label>
            </div>
            @error('bukti_kamera')
                <div class="form-error">{{ $message }}</div>
            @enderror
            @error('bukti_file')
                <div class="form-error">{{ $message }}</div>
            @enderror

            {{-- Preview file yang dipilih --}}
            <div id="filePreviewArea" style="display:none; margin-top:1rem;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.5rem;">
                    <span style="font-size:.85rem; font-weight:600; color:#374151;">
                        <i class="fas fa-eye" style="margin-right:.35rem; color:#6366f1;"></i>Preview File
                    </span>
                    <button type="button" id="clearFilePreview"
                        style="background:none; border:none; cursor:pointer; color:#ef4444; font-size:.8rem; padding:2px 6px; border-radius:4px;"
                        title="Hapus pilihan file">
                        <i class="fas fa-times"></i> Hapus
                    </button>
                </div>
                <div id="filePreviewContent"
                    style="border:1.5px dashed #c7d2fe; border-radius:10px; overflow:hidden; background:#f8fafc; min-height:120px; display:flex; align-items:center; justify-content:center;">
                    <img id="previewImg" src="" alt="Preview"
                        style="display:none; max-width:100%; max-height:400px; object-fit:contain; border-radius:8px;">
                    <iframe id="previewPdf" src="" title="Preview PDF"
                        style="display:none; width:100%; height:400px; border:none;"></iframe>
                    <div id="previewFilename"
                        style="display:none; padding:1.5rem; text-align:center; color:#6b7280; font-size:.875rem;">
                        <i class="fas fa-file fa-2x" style="margin-bottom:.5rem; display:block; color:#9ca3af;"></i>
                        <span id="previewFilenameText"></span>
                    </div>
                </div>
            </div>

            @if ($isEdit && $jurnal->bukti)
                <div class="existing-file">
                    <i class="fas fa-paperclip"></i>
                    <button type="button" class="bukti-preview-btn file-link"
                        data-src="{{ Storage::url($jurnal->bukti) }}" aria-label="Lihat bukti tersimpan">
                        Lihat bukti tersimpan
                    </button>
                </div>

                {{-- Bukti Preview Modal (lazy-load on click) --}}
                <div class="bukti-modal-overlay" id="buktiModalOverlay" aria-hidden="true">
                    <div class="bukti-modal" role="dialog" aria-modal="true" aria-labelledby="buktiModalTitle">
                        <div class="bukti-modal-header">
                            <div class="bukti-modal-title" id="buktiModalTitle">
                                <i class="fas fa-paperclip"></i> Bukti Pembelajaran
                            </div>
                            <button type="button" class="bukti-modal-close" id="buktiModalClose"
                                aria-label="Tutup">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <div class="bukti-modal-body">
                            <img id="buktiModalImg" class="bukti-modal-media" alt="Bukti" loading="lazy"
                                src="" data-loaded="0">
                            <div class="bukti-modal-help">
                                Tip: gambar akan di-load saat tombol <b>Bukti</b> diklik.
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="action-bar">
        <a href="{{ route('guru.jurnal-mengajar.index') }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> 
        </a>
        <button type="submit" id="submitBtn" class="ab-btn ab-btn-primary"
            {{ (empty($jadwalOptions) || (!$isEdit)) ? 'disabled' : '' }}>
            <i class="fas fa-save"></i> {{ $submitLabel }}
        </button>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const schedules = @json($jadwalOptions);
            const selectedSiswa = new Set(@json($selectedSiswaIds));
            const gtkSelect = document.getElementById('gtk_id');
            const scheduleSelect = document.getElementById('jadwal_kbm_id');
            const siswaSelect = document.getElementById('namasiswa');
            const tanggalInput = document.getElementById('tanggal');
            const cameraInput = document.getElementById('bukti_kamera');
            const fileInput = document.getElementById('bukti_file');
            const submitBtn = document.getElementById('submitBtn');
            const isEdit = {{ $isEdit ? 'true' : 'false' }};

            // ── Bukti preview modal (lazy-load on click) ──
            const overlay = document.getElementById('buktiModalOverlay');
            const closeBtn = document.getElementById('buktiModalClose');
            const img = document.getElementById('buktiModalImg');
            const triggerButtons = document.querySelectorAll('.bukti-preview-btn');

            function openModal(src) {
                if (!overlay || !img) return;
                if (img.dataset.loaded !== '1') {
                    img.src = src;
                    img.dataset.loaded = '1';
                } else {
                    img.src = src;
                }
                overlay.classList.add('open');
                overlay.setAttribute('aria-hidden', 'false');
            }

            function closeModal() {
                if (!overlay || !img) return;
                overlay.classList.remove('open');
                overlay.setAttribute('aria-hidden', 'true');
                img.src = '';
                img.dataset.loaded = '0';
            }

            triggerButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const src = this.getAttribute('data-src');
                    if (src) openModal(src);
                });
            });

            closeBtn?.addEventListener('click', closeModal);
            overlay?.addEventListener('click', function(e) {
                if (e.target === overlay) closeModal();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && overlay?.classList.contains('open')) closeModal();
            });

            // ── Jadwal summary ──
            function currentSchedule() {
                return schedules.find((item) => String(item.id) === String(scheduleSelect.value));
            }

            function updateSummary() {
                const schedule = currentSchedule();
                const siswa = schedule ? schedule.siswa : [];
                const total = schedule ? Number(schedule.total_siswa) : 0;

                document.getElementById('summaryKelas').textContent = schedule ? schedule.kelas : '-';
                document.getElementById('summaryPelajaran').textContent = schedule ? schedule.pelajaran : '-';
                document.getElementById('summaryWaktu').textContent = schedule ?
                    `Jam ${schedule.jam_ke}, ${schedule.jam_mulai}-${schedule.jam_selesai}` : '-';

                siswaSelect.innerHTML = '';

                if (!schedule) {
                    const option = document.createElement('option');
                    option.textContent = 'Pilih jadwal terlebih dahulu';
                    option.disabled = true;
                    siswaSelect.appendChild(option);
                } else if (!siswa.length) {
                    const option = document.createElement('option');
                    option.textContent = 'Tidak ada data siswa pada kelas ini';
                    option.disabled = true;
                    siswaSelect.appendChild(option);
                }

                siswa.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.nama;
                    option.selected = selectedSiswa.has(String(item.id));
                    siswaSelect.appendChild(option);
                });

                updateCounters(total);
            }

            function updateCounters(totalOverride) {
                const schedule = currentSchedule();
                const total = typeof totalOverride === 'number' ? totalOverride : (schedule ? Number(schedule.total_siswa) : 0);
                const tidakHadir = Array.from(siswaSelect.selectedOptions).length;
                const hadir = Math.max(total - tidakHadir, 0);

                document.getElementById('countTotal').textContent = total;
                document.getElementById('countTidakHadir').textContent = tidakHadir;
                document.getElementById('countHadir').textContent = hadir;
            }

            scheduleSelect?.addEventListener('change', function() {
                selectedSiswa.clear();
                updateSummary();
                updateSubmitState();
            });

            siswaSelect?.addEventListener('change', function() {
                updateCounters();
            });

            tanggalInput?.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('tanggal', this.value);
                if (gtkSelect?.value) url.searchParams.set('gtk_id', gtkSelect.value);
                window.location.href = url.toString();
            });

            gtkSelect?.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('gtk_id', this.value);
                if (tanggalInput?.value) url.searchParams.set('tanggal', tanggalInput.value);
                window.location.href = url.toString();
            });

            // ── Submit button state (create only) ──
            function hasFile() {
                return (cameraInput?.files?.length > 0) || (fileInput?.files?.length > 0);
            }

            function updateSubmitState() {
                if (!submitBtn || isEdit) return;
                const jadwalOk = !!(scheduleSelect?.value);
                submitBtn.disabled = !(jadwalOk && hasFile());
            }

            // ── File Preview ──
            const previewArea = document.getElementById('filePreviewArea');
            const previewImg = document.getElementById('previewImg');
            const previewPdf = document.getElementById('previewPdf');
            const previewFilename = document.getElementById('previewFilename');
            const previewFilenameText = document.getElementById('previewFilenameText');
            const clearPreviewBtn = document.getElementById('clearFilePreview');

            function showFilePreview(file) {
                if (!file || !previewArea) return;

                previewImg.style.display = 'none';
                previewPdf.style.display = 'none';
                previewFilename.style.display = 'none';
                previewImg.src = '';
                previewPdf.src = '';

                previewArea.style.display = 'block';

                const url = URL.createObjectURL(file);

                if (file.type.startsWith('image/')) {
                    previewImg.src = url;
                    previewImg.style.display = 'block';
                } else if (file.type === 'application/pdf') {
                    previewPdf.src = url;
                    previewPdf.style.display = 'block';
                } else {
                    previewFilenameText.textContent = file.name;
                    previewFilename.style.display = 'block';
                }
            }

            function clearFilePreview() {
                if (!previewArea) return;
                previewImg.style.display = 'none';
                previewPdf.style.display = 'none';
                previewFilename.style.display = 'none';
                previewImg.src = '';
                previewPdf.src = '';
                previewArea.style.display = 'none';

                if (cameraInput) cameraInput.value = '';
                if (fileInput) fileInput.value = '';

                updateSubmitState();
            }

            clearPreviewBtn?.addEventListener('click', clearFilePreview);

            cameraInput?.addEventListener('change', function() {
                if (this.files.length) {
                    if (fileInput) fileInput.value = '';
                    showFilePreview(this.files[0]);
                }
                updateSubmitState();
            });

            fileInput?.addEventListener('change', function() {
                if (this.files.length) {
                    if (cameraInput) cameraInput.value = '';
                    showFilePreview(this.files[0]);
                }
                updateSubmitState();
            });

            // ── Guard submit: alert jika lolos tanpa file (defense in depth) ──
            document.querySelector('.jurnal-form')?.addEventListener('submit', function(e) {
                if (!isEdit && !hasFile()) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Bukti belum dilampirkan',
                        text: 'Silakan ambil foto atau pilih file bukti pembelajaran sebelum menyimpan.',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#f59e0b',
                    });
                }
            });

            // ── SweetAlert untuk error validasi server ──
            @if ($errors->has('bukti_file') || $errors->has('bukti_kamera'))
            Swal.fire({
                icon: 'error',
                title: 'Bukti tidak valid',
                html: `{!! implode('<br>', array_merge($errors->get('bukti_file'), $errors->get('bukti_kamera'))) !!}`,
                confirmButtonText: 'Oke',
                confirmButtonColor: '#ef4444',
            });
            @elseif ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: '{!! implode("<br>", $errors->all()) !!}',
                confirmButtonText: 'Oke',
                confirmButtonColor: '#ef4444',
            });
            @endif

            updateSummary();
            updateSubmitState(); // set initial state on page load
        });
    </script>
@endpush
