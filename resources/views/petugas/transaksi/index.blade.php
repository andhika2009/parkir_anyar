@extends('layout.layout')

@section('title', 'Transaksi Parkir')

@section('content')
<h3 class="fw-bold mb-4">Operasional Transaksi Parkir</h3>

@if(request('selesai'))
    <div class="alert alert-success d-flex justify-content-between align-items-center" id="alertSelesai">
        <div>
            <strong>Struk {{ request('selesai') }} selesai dicetak.</strong>
            Kendaraan <strong>{{ request('plat') }}</strong> sudah keluar dan slot area kembali tersedia.
        </div>
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    </div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white fw-bold">Kendaraan Masuk</div>
    <div class="card-body">
        <form method="POST" action="/petugas/transaksi" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label">No Plat</label>
                <input type="text" name="plat_nomer" id="plat_nomer" value="{{ old('plat_nomer') }}"
                    class="form-control @error('plat_nomer') is-invalid @enderror"
                    placeholder="D 1234 AB"
                    maxlength="11"
                    pattern="^[A-Za-z]{1,2}\s?[0-9]{1,4}\s?[A-Za-z]{0,3}$"
                    title="Format plat Indonesia, contoh: D 1234 AB"
                    style="text-transform: uppercase"
                    required>
                @error('plat_nomer') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Jenis</label>
                <select name="jenis_kendaraan" class="form-select" required>
                    @foreach($tarifs as $t)
                        <option value="{{ $t->jenis_kendaraan }}">{{ $t->jenis_kendaraan }} (Rp {{ number_format($t->tarif_per_jam, 0, ',', '.') }}/jam)</option>
                    @endforeach
                </select>
            </div>
           <div class="col-md-3">
                <label class="form-label">Area</label>
                <select name="id_area" class="form-select" required>
                    @foreach($areas as $a)
                        <option value="{{ $a->id_area }}">
                            {{ $a->nama_area }} ({{ $a->sisa > 0 ? 'sisa ' . $a->sisa . ' slot' : 'PENUH' }})
                        </option>
                    @endforeach
                </select>
                @error('id_area') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100">Simpan Masuk</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white fw-bold">Data Kendaraan Dalam Area Parkir (Aktif)</div>
    <table class="table mb-0">
        <thead>
            <tr><th>No</th><th>No Plat</th><th>Jenis</th><th>Area</th><th>Masuk</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($aktif as $i => $t)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $t->kendaraan->plat_nomer ?? '-' }}</td>
                    <td>{{ $t->kendaraan->jenis_kendaraan ?? '-' }}</td>
                    <td>{{ $t->area->nama_area ?? '-' }}</td>
                    <td>{{ $t->waktu_masuk->format('d/m/Y H:i') }}</td>
                    <td>
                        <form method="POST" action="/petugas/transaksi/{{ $t->id_transaksi }}/keluar" onsubmit="return confirm('Proses kendaraan keluar?')">
                            @csrf
                            @method('PUT')
                            <button class="btn btn-sm btn-danger">Keluar &amp; Cetak Struk</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data kendaraan aktif.</td></tr>
            @endforelse
        </tbody>
       </table>
</div>
    <script>
        const platInput = document.getElementById('plat_nomer');
        const pesanPlat = 'Format plat tidak valid. Contoh yang benar: D 1234 AB';

        platInput.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9 ]/g, '');
            this.setCustomValidity('');   // reset pesan saat mengetik
        });

           platInput.addEventListener('invalid', function () {
        if (this.validity.valueMissing) {
            this.setCustomValidity('No plat wajib diisi.');
        } else {
            this.setCustomValidity(pesanPlat);
        }
    });         

    // Kalau halaman muncul dari cache (tombol back), muat ulang supaya data terbaru
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) window.location.reload();

            // Hapus parameter dari URL supaya pesan tidak muncul lagi saat refresh, dan hilang otomatis
    if (location.search.includes('selesai=')) {
        history.replaceState(null, '', location.pathname);
        setTimeout(function () {
            const el = document.getElementById('alertSelesai');
            if (el) el.remove();
        }, 6000);
    }
    });
</script>
@endsection