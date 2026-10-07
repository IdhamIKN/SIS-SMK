@extends('layouts.app')
@section('title', 'Tambah Absensi Manual')

@push('styles')
    @include('components.event-styles')
    <style>
        .form-wrap {
            padding-top: var(--header-h, 56px);
            padding-bottom: calc(var(--footer-h, 60px)+88px);
            max-width: 640px;
            margin: 0 auto;
        }

        .form-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 14px;
        }

        .form-card h3 {
            font-size: .9rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-row {
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .form-label .req {
            color: #ef4444;
            margin-left: 2px;
        }

        .form-input {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            font-size: .875rem;
            font-family: inherit;
            background: #f8fafc;
            color: #0f172a;
            outline: none;
            box-sizing: border-box;
            transition: border-color .15s;
        }

        .form-input:focus {
            border-color: #0ea5e9;
            background: #fff;
        }

        .form-input.is-error {
            border-color: #ef4444;
        }

        .error-msg {
            color: #dc2626;
            font-size: .72rem;
            margin-top: 4px;
        }

        .siswa-preview {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 10px;
            padding: 10px 14px;
            margin-top: 8px;
            font-size: .82rem;
            display: none;
        }

        .status-preview {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 12px;
            font-size: .82rem;
            display: none;
        }

        .hint {
            font-size: .7rem;
            color: #64748b;
            margin-top: 4px;
        }

        .rule-box {
            background: #fefce8;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 14px;
            font-size: .78rem;
            color: #78350f;
        }

        .rule-box ul {
            margin: 6px 0 0 16px;
        }

        .rule-box li {
            margin-bottom: 3px;
        }

        .action-bar {
            position: fixed;
            bottom: var(--footer-h);
            left: 0;
            right: 0;
            padding: 10px 16px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 8px;
            z-index: 999;
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: .875rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            line-height: 1;
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .ab-btn-green {
            background: #16a34a;
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap form-wrap">
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>Admin</div>
            <h2><i class="fas fa-plus-circle"></i> Tambah Absensi Manual</h2>
            <p>Buat data absensi dengan seluruh rule sistem yang berlaku</p>
        </div>

        {{-- Info Rule --}}
        <div class="rule-box">
            <strong><i class="fas fa-info-circle"></i> Rule yang Diterapkan Otomatis:</strong>
            <ul>
                <li>Status <strong>Terlambat</strong> dihitung otomatis jika jam masuk melewati <em>batas tepat waktu</em>
                    dari School Config</li>
                <li>Status <strong>Alfa</strong> jika jam masuk dikosongkan</li>
                <li>Override manual: pilih status secara eksplisit jika diperlukan</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('admin.absen-manual.store') }}" id="mainForm">
            @csrf

            {{-- Siswa & Tanggal --}}
            <div class="form-card">
                <h3><i class="fas fa-user" style="color:#0ea5e9;"></i> Data Siswa</h3>

                <div class="form-row">
                    <label class="form-label">Kelas <span class="req">*</span></label>
                    <select id="kelasSelect" class="form-input">
                        <option value="">— Pilih Kelas dulu —</option>
                        @foreach ($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <label class="form-label">Siswa <span class="req">*</span></label>
                    <select name="siswa_id" id="siswaSelect" class="form-input @error('siswa_id') is-error @enderror"
                        required>
                        <option value="">— Pilih Siswa —</option>
                    </select>
                    @error('siswa_id')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                    <div class="siswa-preview" id="siswaPreview"></div>
                </div>

                <div class="form-row">
                    <label class="form-label">Tanggal <span class="req">*</span></label>
                    <input type="date" name="tanggal" id="tanggalInput"
                        class="form-input @error('tanggal') is-error @enderror" value="{{ old('tanggal', $tanggal) }}"
                        required>
                    @error('tanggal')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Preview status existing --}}
                <div class="status-preview" id="statusPreview"></div>
            </div>

            {{-- Jam Masuk --}}
            <div class="form-card">
                <h3><i class="fas fa-sign-in-alt" style="color:#16a34a;"></i> Absen Masuk</h3>

                <div class="form-row">
                    <label class="form-label">Jam Masuk</label>
                    <input type="time" name="jam_masuk" id="jamMasukInput"
                        class="form-input @error('jam_masuk') is-error @enderror" value="{{ old('jam_masuk') }}">
                    @error('jam_masuk')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                    <div class="hint">Kosongkan jika siswa tidak hadir (otomatis Alfa)</div>
                </div>

                <div class="form-row">
                    <label class="form-label">Override Status Masuk</label>
                    <select name="status_masuk" class="form-input @error('status_masuk') is-error @enderror">
                        <option value="">— Hitung Otomatis (dari jam masuk) —</option>
                        <option value="hadir" {{ old('status_masuk') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="terlambat" {{ old('status_masuk') === 'terlambat' ? 'selected' : '' }}>Terlambat
                        </option>
                        <option value="izin" {{ old('status_masuk') === 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="sakit" {{ old('status_masuk') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="alfa" {{ old('status_masuk') === 'alfa' ? 'selected' : '' }}>Alfa</option>
                    </select>
                    @error('status_masuk')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                    <div class="hint">Biarkan kosong agar sistem menghitung otomatis berdasarkan jam masuk &amp; batas
                        tepat waktu</div>
                </div>
            </div>

            {{-- Jam Pulang --}}
            <div class="form-card">
                <h3><i class="fas fa-sign-out-alt" style="color:#0ea5e9;"></i> Absen Pulang</h3>

                <div class="form-row">
                    <label class="form-label">Jam Pulang</label>
                    <input type="time" name="jam_pulang" id="jamPulangInput"
                        class="form-input @error('jam_pulang') is-error @enderror" value="{{ old('jam_pulang') }}">
                    @error('jam_pulang')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                    <div class="hint">Kosongkan jika siswa belum/tidak pulang tercatat</div>
                </div>
            </div>

            {{-- Catatan --}}
            <div class="form-card">
                <h3><i class="fas fa-sticky-note" style="color:#f59e0b;"></i> Catatan</h3>
                <div class="form-row">
                    <textarea name="catatan" class="form-input @error('catatan') is-error @enderror" rows="3"
                        placeholder="Catatan opsional...">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </form>
    </div>

    <div class="action-bar">
        <a href="{{ route('admin.absen-manual.index', ['tanggal' => $tanggal]) }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> Batal
        </a>
        <button type="submit" form="mainForm" class="ab-btn ab-btn-green">
            <i class="fas fa-save"></i> Simpan Absensi
        </button>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const kelasSelect = document.getElementById('kelasSelect');
            const siswaSelect = document.getElementById('siswaSelect');
            const tanggalInput = document.getElementById('tanggalInput');
            const siswaPreview = document.getElementById('siswaPreview');
            const statusPreview = document.getElementById('statusPreview');

            // Load siswa by kelas
            kelasSelect.addEventListener('change', function() {
                const kelasId = this.value;
                siswaSelect.innerHTML = '<option value="">Memuat...</option>';
                siswaPreview.style.display = 'none';
                statusPreview.style.display = 'none';

                if (!kelasId) {
                    siswaSelect.innerHTML = '<option value="">— Pilih Siswa —</option>';
                    return;
                }

                fetch(`{{ route('admin.absen-manual.api.search-siswa') }}?kelas_id=${kelasId}&q=`)
                    .then(r => r.json())
                    .then(data => {
                        siswaSelect.innerHTML = '<option value="">— Pilih Siswa —</option>';
                        data.forEach(s => {
                            const opt = document.createElement('option');
                            opt.value = s.id;
                            opt.textContent = s.text;
                            siswaSelect.appendChild(opt);
                        });
                    });
            });

            // Cek status existing saat siswa/tanggal berubah
            function cekStatus() {
                const siswaId = siswaSelect.value;
                const tanggal = tanggalInput.value;
                if (!siswaId || !tanggal) return;

                const url =
                    `{{ route('admin.absen-manual.api.status-siswa') }}?siswa_id=${siswaId}&tanggal=${tanggal}`;
                fetch(url).then(r => r.json()).then(data => {
                    // Preview siswa
                    const opt = siswaSelect.options[siswaSelect.selectedIndex];
                    if (opt && opt.value) {
                        siswaPreview.style.display = 'block';
                        siswaPreview.innerHTML =
                            `<i class="fas fa-user" style="color:#16a34a;margin-right:6px;"></i><strong>${opt.text}</strong>`;
                    }

                    // Preview status existing
                    if (data.ada_record) {
                        const masuk = data.jam_masuk ?
                            `<strong style="color:#16a34a;">${data.jam_masuk}</strong>` :
                            '<span style="color:#94a3b8;">—</span>';
                        const pulang = data.jam_pulang ?
                            `<strong style="color:#0369a1;">${data.jam_pulang}</strong>` :
                            '<span style="color:#94a3b8;">—</span>';
                        const status = data.status_masuk || '-';
                        statusPreview.style.display = 'block';
                        statusPreview.innerHTML =
                            `<i class="fas fa-info-circle" style="color:#2563eb;margin-right:6px;"></i>
                    <strong>Record sudah ada:</strong> Masuk ${masuk} | Pulang ${pulang} | Status <strong>${status}</strong><br>
                    <span style="color:#64748b;font-size:.72rem;">Data akan di-<em>update</em>, bukan diganti seluruhnya.</span>
                    ${data.ada_izin ? `<br><span style="color:#b45309;"><i class="fas fa-file-medical"></i> Ada izin: ${data.jenis_izin}</span>` : ''}`;
                    } else {
                        statusPreview.style.display = 'block';
                        statusPreview.innerHTML =
                            `<i class="fas fa-plus-circle" style="color:#16a34a;margin-right:6px;"></i>Belum ada record — akan dibuat baru.`;
                    }
                });
            }

            siswaSelect.addEventListener('change', cekStatus);
            tanggalInput.addEventListener('change', cekStatus);
        });

        // ── SweetAlert untuk error validasi server ──
        @if ($errors->any())
            if (typeof Swal !== 'undefined' && !window.__swalValidationShown) {
                window.__swalValidationShown = true;
                const errorList = @json($errors->all());
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: '<ul style="padding:0;margin:0;list-style:none;text-align:left;">' +
                        errorList.map(m => '<li style="margin-bottom:4px;">• ' + m + '</li>').join('') +
                        '</ul>',
                    confirmButtonText: 'Oke',
                    confirmButtonColor: '#ef4444',
                });
            }
        @endif
    </script>
@endpush
