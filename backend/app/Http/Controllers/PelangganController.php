<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $pelanggans = Pelanggan::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $pelanggans->where('nama', 'LIKE', "%{$search}%")
                ->orWhere('alamat', 'LIKE', "%{$search}%");
        }

        $pelanggans = $pelanggans->paginate(10);

        if ($pelanggans->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data pelanggan',
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan ditemukan',
            'data' => $pelanggans
        ], 200);
    }

    public function select()
    {
        return response()->json(Pelanggan::select('id', 'nama')->get());
    }

    public function store(Request $request)
    {
        try {
            $validatedData =  $request->validate([
                'nama' => 'required|string|max:50',
                'no_hp' => 'required|digits_between:10,13',
                'alamat' => 'required|string|max:255',
            ], [
                'nama.required' => 'nama pelanggan wajib diisi!',
                'nama.string' => 'nama pelanggan harus berupa teks!',
                'nama.max' => 'nama pelanggan tidak boleh lebih dari 50 karakter',
                'alamat.required' => 'alamat wajib diisi!',
                'alamat.string' => 'alamat harus berupa teks!',
                'alamat.max' => 'alamat tidak boleh lebih dari 255 karakter',
                'no_hp.required' => 'no hp wajib diisi!',
                'no_hp.digits_between' => 'no hp harus minimal 10 digit atau maksimal 13 digit !!',
            ]);

            $pelanggan = Pelanggan::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Data pelanggan berhasil disimpan',
                'data' => $pelanggan
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        // $decrypt = Crypt::decryptString($id);

        $pelanggan = Pelanggan::find($id);

        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Data pelanggan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan ditemukan',
            'data' => $pelanggan
        ], 200);
    }

    public function update(Request $request, $id)
    {
        // $decrypt = Crypt::decryptString($id);

        $pelanggan = Pelanggan::find($id);

        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Data pelanggan tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'nama' => 'required|string|max:50',
            'no_hp' => 'required|digits_between:10,13',
            'alamat' => 'required|string|max:50',
        ], [
                'nama.required' => 'nama pelanggan wajib diisi!',
                'nama.string' => 'nama pelanggan harus berupa teks!',
                'nama.max' => 'nama pelanggan tidak boleh lebih dari 50 karakter',
                'alamat.required' => 'alamat wajib diisi!',
                'alamat.string' => 'alamat harus berupa teks!',
                'alamat.max' => 'alamat tidak boleh lebih dari 50 karakter',
                'no_hp.required' => 'no hp wajib diisi!',
                'no_hp.digits_between' => 'no hp harus minimal 10 digit atau maksimal 13 digit !!',
        ]);

        $pelanggan->nama = $request->nama;
        $pelanggan->no_hp = $request->no_hp;
        $pelanggan->alamat = $request->alamat;
        $pelanggan->save();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengupdate data',
            'data' => $pelanggan
        ], 200);
    }

    public function destroy($id)
    {
        try {
            // $decrypt = Crypt::decryptString($id);

            $pelanggan = Pelanggan::find($id);


            if (!$pelanggan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pelanggan tidak ditemukan.',
                ], 404);
            }

            $pelanggan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data pelanggan berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
