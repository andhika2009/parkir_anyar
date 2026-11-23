<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Kendaraan;
use App\Models\Tarif;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index()
    {
        $aktif = Transaksi::with(['kendaraan', 'tarif', 'area'])
            ->whereNull('waktu_keluar')
            ->latest('waktu_masuk')
            ->get();

        $areas = Area::orderBy('id_area')->get()->map(function ($a) {
        $a->terisi = Transaksi::whereNull('waktu_keluar')->where('id_area', $a->id_area)->count();
        $a->sisa   = max(0, $a->kapasitas - $a->terisi);
        return $a;
});
        $tarifs = Tarif::all();
      return response()
    ->view('petugas.transaksi.index', compact('aktif', 'areas', 'tarifs'))
    ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
    ->header('Pragma', 'no-cache');
    }
 

     public function store(Request $request)
{
    $request->merge([
        'plat_nomer' => strtoupper(preg_replace('/\s+/', ' ', trim($request->plat_nomer))),
    ]);

   $data = $request->validate([
    'plat_nomer'      => ['required', 'string', 'max:11', 'regex:/^[A-Z]{1,2}\s?[0-9]{1,4}\s?[A-Z]{0,3}$/'],
    'jenis_kendaraan' => 'required|string|exists:tb_tarif,jenis_kendaraan',
    'id_area'         => 'required|exists:tb_area_parkir,id_area',
], [
    'plat_nomer.required' => 'No plat wajib diisi.',
    'plat_nomer.regex'    => 'Format plat tidak valid. Contoh: D 1234 AB',
]);

        $plat    = strtoupper(trim($data['plat_nomer']));
        $idUser  = auth()->user()->id_user;

        // Kendaraan yang sama tidak boleh masuk dua kali
        $sudahMasuk = Transaksi::whereNull('waktu_keluar')
            ->whereHas('kendaraan', fn ($q) => $q->where('plat_nomer', $plat))
            ->exists();

        if ($sudahMasuk) {
            return back()->withErrors(['plat_nomer' => 'Kendaraan ini masih tercatat di area parkir.'])->withInput();
        }

        $tarif = Tarif::where('jenis_kendaraan', $data['jenis_kendaraan'])->firstOrFail();
              // Cari area yang masih ada slot: mulai dari area yang dipilih, lalu ke area berikutnya
        $areaDipilih = Area::findOrFail($data['id_area']);
        $areaTujuan  = null;

    $kandidat = Area::where('id_area', '>=', $areaDipilih->id_area)
        ->orderBy('id_area')
        ->get();

    foreach ($kandidat as $a) {
        $terisi = Transaksi::whereNull('waktu_keluar')->where('id_area', $a->id_area)->count();
        if ($terisi < $a->kapasitas) {
            $areaTujuan = $a;
            break;
        }
    }

    if (!$areaTujuan) {
        return back()
            ->withErrors(['id_area' => 'Semua area dari ' . $areaDipilih->nama_area . ' sampai terakhir sudah penuh.'])
            ->withInput();
    }
        $kendaraan = Kendaraan::firstOrCreate(
            ['plat_nomer' => $plat],
            ['jenis_kendaraan' => $data['jenis_kendaraan'], 'id_user' => $idUser]
        );

       Transaksi::create([
    'id_kendaraan' => $kendaraan->id_kendaraan,
    'id_tarif'     => $tarif->id_tarif,
    'id_area'      => $areaTujuan->id_area,
    'id_user'      => $idUser,
    'waktu_masuk'  => now(),
    'status'       => 'masuk',
]);

$pesan = 'Kendaraan masuk dicatat di ' . $areaTujuan->nama_area . '.';
if ($areaTujuan->id_area != $areaDipilih->id_area) {
    $pesan = $areaDipilih->nama_area . ' penuh, kendaraan dialihkan ke ' . $areaTujuan->nama_area . '.';
}

return redirect('/petugas/transaksi')->with('success', $pesan);
}

    public function keluar($id)
    {
        $trx = Transaksi::with('tarif')->findOrFail($id);

        if ($trx->waktu_keluar) {
            return redirect('/petugas/transaksi/' . $id . '/struk');
        }

        $keluar = now();
        $jam    = max(1, (int) ceil($trx->waktu_masuk->diffInMinutes($keluar) / 60));

        $trx->update([
            'waktu_keluar' => $keluar,
            'durasi_jam'   => $jam,
            'biaya_total'  => $jam * $trx->tarif->tarif_per_jam,
            'status'       => 'keluar',
        ]);

        return redirect('/petugas/transaksi/' . $id . '/struk');
    }

// TEMPEL FUNGSI STRUK DI SINI
public function struk($id)
{
    $trx = Transaksi::with(['kendaraan', 'tarif', 'area'])
        ->findOrFail($id);

    if (!$trx->waktu_keluar) {
        return redirect('/petugas/transaksi')
            ->with('error', 'Kendaraan belum keluar.');
    }

    $waktuMasuk = \Carbon\Carbon::parse($trx->waktu_masuk);
    $waktuKeluar = \Carbon\Carbon::parse($trx->waktu_keluar);

    $durasiJam = $trx->durasi_jam
        ?? max(1, (int) ceil($waktuMasuk->diffInMinutes($waktuKeluar) / 60));

    return view('petugas.transaksi.struk', compact(
        'trx',
        'waktuMasuk',
        'waktuKeluar',
        'durasiJam'
    ));
}
} 