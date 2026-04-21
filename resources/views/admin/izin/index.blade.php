@extends('layouts.app')

@section('title', 'Verifikasi Pengajuan Izin')

@section('content')
<div class="container-fluid px-4">
    <h2 class="mb-4">
        <i class="ti ti-file-check me-2"></i>
        Verifikasi Pengajuan Izin
    </h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Pengajuan Menunggu Persetujuan ({{ $izin->total() }})</h5>
        </div>
        <div class="card-body">
            @if($izin->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Tanggal Izin</th>
                                <th>Jenis</th>
                                <th>Alasan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($izin as $item)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $item->siswa->nama_lengkap }}</strong><br>
                                        <small class="text-muted">{{ $item->siswa->nis }}</small>
                                    </div>
                                </td>
                                <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $item->tanggal_izin->format('d/m/Y') }}</td>
                                <td><span class="badge bg-info">{{ $item->jenis_label }}</span></td>
                                <td>{{ Str::limit($item->alasan, 80) }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <form action="{{ route('admin.izin.update', $item) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="disetujui">
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Setujui izin ini?')">
                                                <i class="ti ti-check"></i> Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.izin.update', $item) }}" method="POST" class="d-inline ms-1">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="ditolak">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tolak izin ini?')">
                                                <i class="ti ti-x"></i> Tolak
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $izin->appends(request()->query())->links() }}
            @else
                <div class="text-center py-5">
                    <i class="ti ti-checks display-1 text-success mb-3"></i>
                    <h5 class="text-muted">Tidak ada pengajuan menunggu</h5>
                    <p class="text-muted">Semua pengajuan izin sudah diproses.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

