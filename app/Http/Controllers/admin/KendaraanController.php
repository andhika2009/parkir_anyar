<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class KendaraanController extends Controller
{
    private function aturan()
    {
        return [
            'plat_nomor'      => ['required', 'string', 'max:20', 'regex:/^[A-Z]{1,2} ?[0-9]{1,4} ?[A-Z]{0,3}$/'],
            'jenis_kendaraan' => ['required', 'string', 'max:50'],
            'pemilik'         => ['nullable', 'string', 'max:100'],
        ];
    }

    private function pesan()
    {
        return [
            'plat_nomor.required'      => 'Plat nomor wajib diisi.',
            'plat_nomor.string'        => 'Plat nomor tidak valid.',
            'plat_nomor.max'           => 'Plat nomor maksimal 20 karakter.',
            'plat_nomor.regex'         => 'Format plat nomor tidak valid. Contoh yang benar: B 1234 ABC.',
            'jenis_kendaraan.required' => 'Jenis kendaraan wajib dipilih.',
            'jenis_kendaraan.string'   => 'Jenis kendaraan tidak valid.',
            'jenis_kendaraan.max'      => 'Jenis kendaraan maksimal 50 karakter.',
            'pemilik.string'           => 'Nama pemilik tidak valid.',
            'pemilik.max'              => 'Nama pemilik maksimal 100 karakter.',
        ];
    }

    private function rapikanPlat(Request $request)
    {
        $plat = strtoupper(trim(preg_replace('/\s+/', ' ', (string) $request->plat_nomor)));
        $request->merge(['plat_nomor' => $plat]);
    }

    public function index()
    {
        $kendaraans = DB::table('tb_kendaraan')
            ->select('id_kendaraan', 'plat_nomer as plat_nomor', 'jenis_kendaraan', 'warna_kendaraan', 'pemilik')
            ->orderBy('id_kendaraan')
            ->get();

        return view('admin.kendaraan.index', compact('kendaraans'));
    }

    public function create()
    {
        return view('admin.kendaraan.create');
    }

    public function store(Request $request)
    {
        $this->rapikanPlat($request);
        $request->validate($this->aturan(), $this->pesan());

        DB::table('tb_kendaraan')->insert([
            'plat_nomer'      => $request->plat_nomor,
            'jenis_kendaraan' => $request->jenis_kendaraan,
            'pemilik'         => $request->pemilik ?? 'Pengunjung',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()->route('admin.kendaraan.index')->with('success', 'Kendaraan berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $k = DB::table('tb_kendaraan')
            ->select('id_kendaraan', 'plat_nomer as plat_nomor', 'jenis_kendaraan', 'warna_kendaraan', 'pemilik')
            ->where('id_kendaraan', $id)
            ->first();

        abort_if(!$k, 404);

        $k->id = $k->id_kendaraan;
        $kendaraan = $k;

        return view('admin.kendaraan.edit', compact('kendaraan'));
    }

    public function update(Request $request, $id)
    {
        $this->rapikanPlat($request);
        $request->validate($this->aturan(), $this->pesan());

        DB::table('tb_kendaraan')->where('id_kendaraan', $id)->update([
            'plat_nomer'      => $request->plat_nomor,
            'jenis_kendaraan' => $request->jenis_kendaraan,
            'pemilik'         => $request->pemilik,
            'updated_at'      => now(),
        ]);

        return redirect()->route('admin.kendaraan.index')->with('success', 'Data kendaraan berhasil diupdate!');
    }

    public function destroy($id)
    {
        try {
            DB::table('tb_kendaraan')->where('id_kendaraan', $id)->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Kendaraan tidak bisa dihapus karena sudah punya riwayat transaksi.');
        }

        return redirect()->route('admin.kendaraan.index')->with('success', 'Data kendaraan berhasil dihapus!');
    }
}