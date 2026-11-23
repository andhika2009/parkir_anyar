@extends('layout.layout')

@section('content')
@php
    $totalPendapatan = collect($laporan)->sum(fn ($i) => ((object) $i)->biaya_total ?? 0);
    $pencetak = auth()->user()->nama_lengkap ?? auth()->user()->user_name ?? 'owner';
@endphp

<style>
    @media print {
        .no-print,
        .sidebar,
        #sidebar,
        #sidebarMenu,
        nav,
        .navbar,
        .btn {
            display: none !important;
        }

        body { background: white !important; }
        .card { box-shadow: none !important; border: none !important; }
        .container, .container-fluid { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        main, .content, .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        .table-dark th { background: #212529 !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge { border: 1px solid #999; color: black !important; background: white !important; }
        @page { size: A4 landscape; margin: 10mm; }
    }
</style>

<div class="container py-4">

    {{-- Kop laporan, hanya tampil saat dicetak --}}
    <div class="d-none d-print-block text-center mb-3">
        <h3 class="fw-bold mb-0">SISTEM PARKIR</h3>
        <p class="mb-0">Laporan & Rekapitulasi Pembayaran Parkir</p>
        <small>Dicetak oleh: {{ $pencetak }} | {{ now()->format('d/m/Y H:i') }}</small>
        <div>
            <small>
                Periode:
                {{ request('dari') ? \Carbon\Carbon::parse(request('dari'))->format('d/m/Y') : 'Awal' }}
                s/d
                {{ request('sampai') ? \Carbon\Carbon::parse(request('sampai'))->format('d/m/Y') : 'Sekarang' }}
            </small>
        </div>
        <hr>
    </div>

    {{-- Filter periode, tidak ikut tercetak --}}
    <form method="GET" action="{{ url()->current() }}" class="card card-body shadow-sm mb-3 no-print">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1">Dari tanggal</label>
                <input type="date" name="dari" value="{{ request('dari') }}" class="form-control @error('dari') is-invalid @enderror">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Sampai tanggal</label>
                <input type="date" name="sampai" value="{{ request('sampai') }}" class="form-control @error('sampai') is-invalid @enderror">
                @error('sampai') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Tampilkan</button>
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-bold fs-5">📄 Laporan & Rekapitulasi Pembayaran Parkir</span>
            <button type="button" class="btn btn-primary no-print" onclick="window.print()">
                🖨️ Cetak Laporan
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>No. Struk / Kode</th>
                            <th>Plat Nomor</th>
                            <th>Waktu & Tanggal</th>
                            <th>Petugas</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($laporan as $item)
                        @php $row = (object) $item; @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>TRX-{{ $row->id_transaksi ?? $row->id ?? '-' }}</td>
                            <td class="fw-bold">{{ $row->plat_nomer ?? 'N/A' }}</td>
                            <td>{{ $row->waktu_masuk ?? $row->created_at ?? '-' }}</td>
                            <td>{{ $row->nama_petugas ?? '-' }}</td>
                            <td>Rp {{ number_format($row->biaya_total ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-{{ in_array(strtolower($row->status ?? ''), ['keluar', 'selesai']) ? 'success' : 'warning' }}">
                                    {{ ucfirst($row->status ?? 'Aktif') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data transaksi pembayaran parkir.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($laporan))
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="5" class="text-end">TOTAL PENDAPATAN</td>
                            <td colspan="2">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection