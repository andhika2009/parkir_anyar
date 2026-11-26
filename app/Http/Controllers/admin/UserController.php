<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.user.index', compact('users'));
    }

    public function create()
    {
        return view('admin.user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'user_name'    => 'required|string|max:255|unique:tb_users,user_name',
            'password'     => 'required|string|min:4',
            'role'         => 'required|in:admin,petugas,owner',
        ]);

        User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'user_name'    => $request->user_name,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'status_aktif' => '1',
        ]);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return view('admin.user.show', compact('user'));
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('admin.user.edit', compact('user'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'user_name'    => 'required|string|max:255|unique:tb_users,user_name,' . $user->id_user . ',id_user',
            'password'     => 'nullable|string|min:4',
            'role'         => 'required|in:admin,petugas,owner',
        ]);

        $user->nama_lengkap = $request->nama_lengkap;
        $user->user_name    = $request->user_name;
        $user->role         = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.user.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return back()->with('error', 'Akun admin tidak boleh dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }

    public function toggle(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return back()->with('error', 'Akun admin tidak bisa dinonaktifkan.');
        }

        $user->status_aktif = $user->status_aktif ? 0 : 1;
        $user->save();

        return back()->with('success', 'Status ' . $user->nama_lengkap . ' sekarang ' . ($user->status_aktif ? 'Aktif' : 'Nonaktif') . '.');
    }
}