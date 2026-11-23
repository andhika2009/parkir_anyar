<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'dari'   => 'nullable|date',
            'sampai' => 'nullable|date|after_or_equal:dari',
        ], [
            'sampai.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
        ]);

        $query = DB::table('tb_transaksi')
            ->leftJoin('tb_tarif', 'tb_transaksi.id_tarif', '=', 'tb_tarif.id_tarif')
            ->leftJoin('tb_kendaraan', 'tb_transaksi.id_kendaraan', '=', 'tb_kendaraan.id_kendaraan')
            ->leftJoin('tb_users', 'tb_transaksi.id_user', '=', 'tb_users.id_user')
            ->select(
                'tb_transaksi.*',
                'tb_tarif.jenis_kendaraan',
                'tb_kendaraan.plat_nomer',
                'tb_users.nama_lengkap as nama_petugas'
            );

        if ($request->filled('dari')) {
            $query->whereDate('tb_transaksi.waktu_masuk', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tb_transaksi.waktu_masuk', '<=', $request->sampai);
        }

        $laporan = $query->orderBy('tb_transaksi.id_transaksi')->get();

        return view('owner.laporan.index', compact('laporan'));
    }
}