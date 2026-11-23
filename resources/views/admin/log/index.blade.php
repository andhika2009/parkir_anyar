@extends('layout.layout')
@section('title', 'Log Aktivitas')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="m-0"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Log Aktivitas Sistem</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Aktivitas</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
           @forelse($logs as $log)
                    <tr>
                        <td>{{ $logs->firstItem() + $loop->index }}</td>
                        <td class="text-muted">{{ \Carbon\Carbon::parse($log->waktu)->format('d M Y H:i:s') }}</td>
                        <td><strong>{{ $log->user->nama_lengkap ?? 'System' }}</strong></td>
                        <td>{{ $log->aktivitas }}</td>
                        <td><code>{{ $log->ip_address }}</code></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada log aktivitas.</td>
                    </tr>
             @endforelse
                </tbody>
                  </table>
     </div>
        <div class="d-flex justify-content-between align-items-center p-3">
            <small class="text-muted">
                Menampilkan {{ $logs->firstItem() }} - {{ $logs->lastItem() }} dari {{ $logs->total() }} data
            </small>
            {{ $logs->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection