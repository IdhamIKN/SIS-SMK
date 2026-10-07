@extends('layouts.app')

@section('title', 'Import Jadwal KBM')

@push('styles')
    @include('components.event-styles')
    <style>
        .imp {
            font-family: inherit;
        }

        /* Strip */
        .imp .imp-strip {
            padding: 20px 20px 28px;
            background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0ea5e9 100%);
            position: relative;
            overflow: hidden;
        }

        .imp .imp-strip::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .imp .imp-live {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 600;
            color: rgba(255, 255, 255, .9);
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .imp .imp-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #7dd3fc;
            display: inline-block;
        }

        .imp .imp-strip h2 {
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 4px;
            position: relative;
            z-index: 1;
        }

        .imp .imp-strip p {
            font-size: .8rem;
            color: rgba(255, 255, 255, .65);
            margin: 0;
            position: relative;
            z-index: 1;
        }

        /* Alert */
        .imp .imp-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: .83rem;
            margin: 12px 16px 0;
        }

        .imp .imp-alert.err {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .imp .imp-alert.ok {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .imp .imp-alert ul {
            margin: 5px 0 0 16px;
            font-size: .78rem;
        }

        /* Card */
        .imp .imp-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            margin: 14px 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .imp .imp-chead {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px 11px;
            border-bottom: 1px solid #f1f5f9;
        }

        .imp .imp-cico {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }

        .imp .imp-chead h3 {
            margin: 0;
            font-size: .9rem;
            font-weight: 700;
            color: #0f172a;
        }

        .imp .imp-cbody {
            padding: 16px;
        }

        /* Field */
        .imp .imp-lbl {
            display: block;
            font-size: .8rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }

        /* Drop zone */
        .imp .imp-drop {
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            background: #f8fafc;
            padding: 32px 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
            position: relative;
        }

        .imp .imp-drop:hover,
        .imp .imp-drop.drag-over {
            border-color: #0ea5e9;
            background: #f0f9ff;
        }

        .imp .imp-drop input[type="file"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .imp .imp-drop-ico {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #e0f2fe;
            color: #0369a1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin: 0 auto 12px;
        }

        .imp .imp-drop p {
            margin: 0 0 4px;
            font-size: .88rem;
            font-weight: 700;
            color: #0f172a;
        }

        .imp .imp-drop small {
            font-size: .74rem;
            color: #64748b;
        }

        .imp .imp-drop .imp-filename {
            display: none;
            margin-top: 10px;
            font-size: .8rem;
            font-weight: 700;
            color: #0369a1;
            background: #e0f2fe;
            border-radius: 8px;
            padding: 6px 12px;
        }

        /* Info grid */
        .imp .imp-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .imp .imp-info-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
        }

        .imp .imp-info-item .lbl {
            font-size: .7rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .imp .imp-info-item .val {
            font-size: .82rem;
            font-weight: 700;
            color: #0f172a;
        }

        .imp .imp-info-item .val.ok {
            color: #15803d;
        }

        .imp .imp-info-item .val.warn {
            color: #b45309;
        }

        /* Kolom table */
        .imp .imp-col-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .8rem;
        }

        .imp .imp-col-table th {
            background: #f8fafc;
            color: #475569;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .imp .imp-col-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            color: #334155;
        }

        .imp .imp-col-table tr:last-child td {
            border-bottom: none;
        }

        .imp .imp-badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 700;
        }

        .imp .imp-badge.req {
            background: #fee2e2;
            color: #be123c;
        }

        .imp .imp-badge.opt {
            background: #f1f5f9;
            color: #475569;
        }

        /* Action bar */
        .imp .imp-bar {
            position: fixed;
            bottom: var(--footer-h, 60px);
            left: 0;
            right: 0;
            padding: 10px 16px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            z-index: 100;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        .imp .imp-ab {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: .875rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            line-height: 1;
        }

        .imp .imp-ab.back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            flex: 0 0 auto;
            padding: 12px 18px;
        }

        .imp .imp-ab.tmpl {
            background: #ecfdf5;
            color: #15803d;
            border: 1px solid #bbf7d0;
            flex: 0 0 auto;
        }

        .imp .imp-ab.submit {
            background: linear-gradient(135deg, #0369a1, #0ea5e9);
            color: #fff;
            box-shadow: 0 3px 12px rgba(14, 165, 233, .3);
        }

        .imp .imp-ab:active {
            transform: scale(.97);
        }

        .imp .imp-ab.submit:hover {
            filter: brightness(1.08);
        }
    </style>
@endpush

@section('content')
    <div class="imp" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- Strip --}}
        <div class="imp-strip">
            <div class="imp-live"><span class="imp-dot"></span> Import Jadwal</div>
            <h2><i class="fas fa-file-import"></i> Import Jadwal KBM</h2>
            <p>Upload file Excel/CSV untuk menambahkan jadwal secara massal</p>
        </div>

        {{-- Error validasi file --}}
        @if ($errors->any())
            <div class="imp-alert err">
                <i class="fas fa-exclamation-circle" style="margin-top:2px;"></i>
                <div>
                    <strong>Upload gagal:</strong>
                    <ul>
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="imp-alert err">
                <i class="fas fa-times-circle" style="margin-top:2px;"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        {{-- Info ringkas tahun ajaran & jam --}}
        <div class="imp-card">
            <div class="imp-chead">
                <div class="imp-cico" style="background:#e0f2fe;color:#0369a1;">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h3>Informasi Data Master</h3>
            </div>
            <div class="imp-cbody">
                <div class="imp-info-grid">
                    <div class="imp-info-item">
                        <div class="lbl">Tahun Ajaran Aktif</div>
                        <div class="val ok">{{ $tahunAjaran }}</div>
                    </div>
                    <div class="imp-info-item">
                        <div class="lbl">Jam Pelajaran Aktif</div>
                        <div class="val ok">{{ $jamList->count() }} jam tersedia</div>
                    </div>
                </div>
                <div style="margin-top:10px;font-size:.76rem;color:#64748b;line-height:1.6;">
                    <i class="fas fa-lightbulb" style="color:#f59e0b;"></i>
                    Kolom <strong>jam_mulai</strong> dan <strong>jam_selesai</strong> diisi dengan
                    <strong>nomor urut jam</strong> (contoh: <code>1</code>, <code>2</code>, <code>3</code>)
                    atau nama jam sesuai tabel jam pelajaran di bawah.
                </div>

                {{-- Tabel referensi jam --}}
                @if ($jamList->count())
                    <div style="margin-top:12px;overflow-x:auto;">
                        <table class="imp-col-table">
                            <thead>
                                <tr>
                                    <th>ID / Nama Jam</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Shift</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jamList as $jam)
                                    <tr>
                                        <td><strong>{{ $jam->id_jam }}</strong> — {{ $jam->nama_jam }}</td>
                                        <td>{{ $jam->time_in ? $jam->time_in->format('H:i') : '—' }}</td>
                                        <td>{{ $jam->time_out ? $jam->time_out->format('H:i') : '—' }}</td>
                                        <td>{{ $jam->shif }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Panduan kolom --}}
        <div class="imp-card">
            <div class="imp-chead">
                <div class="imp-cico" style="background:#fef3c7;color:#b45309;">
                    <i class="fas fa-table"></i>
                </div>
                <h3>Panduan Kolom File</h3>
            </div>
            <div class="imp-cbody" style="padding:0;overflow-x:auto;">
                <table class="imp-col-table">
                    <thead>
                        <tr>
                            <th>Nama Kolom</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Contoh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>nama_kelas</code></td>
                            <td><span class="imp-badge req">Wajib</span></td>
                            <td>Nama kelas persis seperti di data master</td>
                            <td>X RPL 1</td>
                        </tr>
                        <tr>
                            <td><code>kd_guru</code></td>
                            <td><span class="imp-badge opt">Opsional*</span></td>
                            <td>Kode guru (jika tersedia)</td>
                            <td>GR001</td>
                        </tr>
                        <tr>
                            <td><code>nama_guru</code></td>
                            <td><span class="imp-badge opt">Opsional*</span></td>
                            <td>Nama lengkap guru (dipakai jika kd_guru kosong)</td>
                            <td>Budi Santoso</td>
                        </tr>
                        <tr>
                            <td><code>kode_mapel</code></td>
                            <td><span class="imp-badge opt">Opsional*</span></td>
                            <td>Kode mata pelajaran</td>
                            <td>MTK</td>
                        </tr>
                        <tr>
                            <td><code>nama_mapel</code></td>
                            <td><span class="imp-badge opt">Opsional*</span></td>
                            <td>Nama mata pelajaran (dipakai jika kode_mapel kosong)</td>
                            <td>Matematika</td>
                        </tr>
                        <tr>
                            <td><code>hari</code></td>
                            <td><span class="imp-badge req">Wajib</span></td>
                            <td>Hari dalam seminggu</td>
                            <td>Senin</td>
                        </tr>
                        <tr>
                            <td><code>jam_mulai</code></td>
                            <td><span class="imp-badge req">Wajib</span></td>
                            <td>ID atau nama jam mulai</td>
                            <td>1</td>
                        </tr>
                        <tr>
                            <td><code>jam_selesai</code></td>
                            <td><span class="imp-badge req">Wajib</span></td>
                            <td>ID atau nama jam selesai</td>
                            <td>3</td>
                        </tr>
                        <tr>
                            <td><code>tahun_ajaran</code></td>
                            <td><span class="imp-badge req">Wajib</span></td>
                            <td>Format YYYY/YYYY</td>
                            <td>{{ $tahunAjaran }}</td>
                        </tr>
                        <tr>
                            <td><code>semester</code></td>
                            <td><span class="imp-badge req">Wajib</span></td>
                            <td>1 atau 2</td>
                            <td>1</td>
                        </tr>
                    </tbody>
                </table>
                <div style="padding:10px 14px;font-size:.72rem;color:#64748b;">
                    * Minimal salah satu dari kd_guru/nama_guru dan kode_mapel/nama_mapel harus diisi.
                </div>
            </div>
        </div>

        {{-- Form Upload --}}
        <form id="importForm" method="POST" action="{{ route('admin.jadwal-kbm.import.process') }}"
            enctype="multipart/form-data">
            @csrf
            <div class="imp-card">
                <div class="imp-chead">
                    <div class="imp-cico" style="background:#dcfce7;color:#15803d;">
                        <i class="fas fa-upload"></i>
                    </div>
                    <h3>Upload File</h3>
                </div>
                <div class="imp-cbody">
                    <label class="imp-lbl">File Excel / CSV <span style="color:#ef4444;">*</span></label>
                    <div class="imp-drop" id="dropZone">
                        <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" required>
                        <div class="imp-drop-ico"><i class="fas fa-cloud-upload-alt"></i></div>
                        <p>Klik atau seret file ke sini</p>
                        <small>Format: .xlsx, .xls, .csv &nbsp;•&nbsp; Maks. 5 MB</small>
                        <div class="imp-filename" id="fileName"></div>
                    </div>

                    <div style="margin-top:12px;padding:10px 12px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;font-size:.76rem;color:#92400e;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Jadwal yang <strong>bertabrakan</strong> dengan jadwal kelas atau guru yang sudah ada akan
                        <strong>dilewati</strong> dan dicatat di log error. Data yang sama persis (guru + mapel sama) akan
                        <strong>diperbarui</strong>.
                    </div>
                </div>
            </div>
        </form>

        {{-- Action bar --}}
        <div class="imp-bar">
            <a href="{{ route('admin.jadwal-kbm.index') }}" class="imp-ab back">
                <i class="fas fa-times"></i>
            </a>
            <a href="{{ route('admin.jadwal-kbm.template') }}" class="imp-ab tmpl">
                <i class="fas fa-download"></i> Template
            </a>
            <button type="submit" form="importForm" class="imp-ab submit" id="btnSubmit">
                <i class="fas fa-file-import"></i> Mulai Import
            </button>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header-auto-show');
            if (header) header.classList.add('header-active');

            const dropZone  = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');
            const fileName  = document.getElementById('fileName');
            const btnSubmit = document.getElementById('btnSubmit');

            // Tampilkan nama file yang dipilih
            fileInput.addEventListener('change', function() {
                if (this.files.length) {
                    fileName.textContent = this.files[0].name;
                    fileName.style.display = 'block';
                } else {
                    fileName.style.display = 'none';
                }
            });

            // Drag & drop visual
            ['dragenter', 'dragover'].forEach(function(evt) {
                dropZone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    dropZone.classList.add('drag-over');
                });
            });

            ['dragleave', 'drop'].forEach(function(evt) {
                dropZone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    dropZone.classList.remove('drag-over');
                    if (evt === 'drop' && e.dataTransfer.files.length) {
                        fileInput.files = e.dataTransfer.files;
                        fileName.textContent = e.dataTransfer.files[0].name;
                        fileName.style.display = 'block';
                    }
                });
            });

            // Konfirmasi sebelum submit
            btnSubmit.addEventListener('click', function(e) {
                if (!fileInput.files.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'File belum dipilih',
                        text: 'Pilih file Excel atau CSV terlebih dahulu.',
                        confirmButtonText: 'OK'
                    });
                    e.preventDefault();
                    return;
                }

                Swal.fire({
                    icon: 'question',
                    title: 'Mulai Import?',
                    html: 'File <strong>' + fileInput.files[0].name + '</strong> akan diproses.<br>Jadwal yang bertabrakan akan dilewati.',
                    showCancelButton: true,
                    confirmButtonColor: '#0369a1',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fas fa-file-import"></i> Import',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then(function(result) {
                    if (result.isConfirmed) {
                        btnSubmit.disabled = true;
                        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                        document.getElementById('importForm').submit();
                    }
                });

                e.preventDefault();
            });
        });
    </script>
@endpush
