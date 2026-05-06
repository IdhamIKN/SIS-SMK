@extends('layouts.app')

@section('title', 'Preview Import Siswa')

@push('styles')
    <style>
        .preview-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            display: flex;
            gap: 20px;
        }

        .summary-item {
            flex: 1;
            text-align: center;
        }

        .summary-number {
            font-size: 2rem;
            font-weight: 800;
            color: #1e293b;
        }

        .summary-label {
            font-size: .875rem;
            color: #64748b;
            margin-top: 4px;
        }

        .valid { color: #16a34a; }
        .invalid { color: #dc2626; }

        .preview-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1);
        }

        .preview-table th,
        .preview-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .preview-table th {
            background: #f8fafc;
            font-weight: 700;
            font-size: .875rem;
            color: #374151;
        }

        .preview-table tbody tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: .75rem;
            font-weight: 600;
        }

        .status-valid {
            background: #dcfce7;
            color: #166534;
        }

        .status-invalid {
            background: #fef2f2;
            color: #991b1b;
        }

        .errors-list {
            margin-top: 24px;
        }

        .error-item {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .error-row {
            font-weight: 600;
            color: #dc2626;
            margin-bottom: 8px;
        }

        .error-messages {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .error-messages li {
            color: #7f1d1d;
            font-size: .875rem;
            margin-bottom: 4px;
        }

        .action-buttons {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            padding: 16px 20px;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            z-index: 1000;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: .875rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .2s;
        }

        .btn-primary {
            background: #7c3aed;
            color: #fff;
        }

        .btn-primary:hover {
            background: #6d28d9;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .btn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }
    </style>
@endpush

@section('content')
    <div class="preview-container" style="padding-bottom: 100px;">

        {{-- Page Header --}}
        <div class="page-strip page-strip-izin" style="margin-bottom: 24px;">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ now()->translatedFormat('d F Y') }}
            </div>
            <h2><i class="fas fa-eye"></i> Preview Import Siswa</h2>
            <p>Periksa data siswa sebelum melakukan import</p>
        </div>

        {{-- Summary --}}
        <div class="summary-card">
            <div class="summary-item">
                <div class="summary-number valid">{{ count($validData) }}</div>
                <div class="summary-label">Data Akan Diimpor</div>
            </div>
            <div class="summary-item">
                <div class="summary-number invalid">{{ count($errors) }}</div>
                <div class="summary-label">Data Dilewati (Duplikat/Error)</div>
            </div>
            <div class="summary-item">
                <div class="summary-number">{{ count($validData) + count($errors) }}</div>
                <div class="summary-label">Total Data</div>
            </div>
        </div>
            <div class="summary-item">
                <div class="summary-number invalid">{{ count($importErrors) }}</div>
                <div class="summary-label">Data Error</div>
            </div>
            <div class="summary-item">
                <div class="summary-number">{{ count($validData) + count($importErrors) }}</div>
                <div class="summary-label">Total Data</div>
            </div>
        </div>

        {{-- Errors List --}}
        @if(count($importErrors) > 0)
            <div class="errors-list">
                <h3 style="color: #dc2626; margin-bottom: 16px;"><i class="fas fa-exclamation-triangle"></i> Data dengan Error:</h3>
                @foreach($importErrors as $error)
                    <div class="error-item">
                        <div class="error-row">Baris {{ $error['row'] }}: {{ $error['data']['nama_lengkap'] ?? 'N/A' }} ({{ $error['data']['nisn'] ?? 'N/A' }})</div>
                        <ul class="error-messages">
                            @foreach($error['errors'] as $errMsg)
                                <li>{{ $errMsg }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Preview Table --}}
        <div style="overflow-x: auto; margin-top: 24px;">
            <table class="preview-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NISN</th>
                        <th>Nama Lengkap</th>
                        <th>Jenis Kelamin</th>
                        <th>Kelas</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach($validData as $siswa)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $siswa['nisn'] }}</td>
                            <td>{{ $siswa['nama_lengkap'] }}</td>
                            <td>{{ $siswa['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td>{{ \App\Models\Kelas::find($siswa['kelas_id'])->nama_kelas ?? 'N/A' }}</td>
                            <td><span class="status-badge status-valid">Valid</span></td>
                        </tr>
                    @endforeach
                    @foreach($importErrors as $error)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $error['data']['nisn'] ?? '-' }}</td>
                            <td>{{ $error['data']['nama_lengkap'] ?? '-' }}</td>
                            <td>{{ $error['data']['jenis_kelamin'] ?? '-' }}</td>
                            <td>{{ $error['data']['nama_kelas'] ?? '-' }}</td>
                            <td><span class="status-badge status-invalid">Error</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    {{-- Action Buttons --}}
    <div class="action-buttons">
        <a href="{{ route('siswa.import.form') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali Upload File
        </a>
        <div>
            @if(count($validData) > 0)
                <form method="POST" action="{{ route('siswa.import.process') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin mengimpor {{ count($validData) }} data siswa? Data duplikat akan dilewati.')">
                        <i class="fas fa-upload"></i> Import {{ count($validData) }} Siswa
                    </button>
                </form>
            @else
                <button class="btn btn-primary" disabled>
                    <i class="fas fa-upload"></i> Tidak Ada Data Valid untuk Import
                </button>
            @endif
        </div>
    </div>
@endsection
