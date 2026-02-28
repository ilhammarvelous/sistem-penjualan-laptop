<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $barangs = Barang::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $barangs->where(function ($query) use ($search) {
                $query->where('kode_barang', 'LIKE', "%{$search}%")
                    ->orWhere('nama_barang', 'LIKE', "%{$search}%")
                    ->orWhere('harga', 'LIKE', "%{$search}%");
            });
        }

        $barangs = $barangs->paginate(10);

        // Ubah koleksi agar ambil total_stok_keseluruhan terakhir dari barang_details
        $barangs->getCollection()->transform(function ($barang) {
            $lastDetail = $barang->barang_details()->orderByDesc('id')->first();
            $barang->total_stok_keseluruhan = $lastDetail->total_stok_keseluruhan ?? 0;
            return $barang;
        });

        if ($barangs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data barang',
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data barang ditemukan',
            'data' => $barangs
        ], 200);
    }

    public function select()
    {
        // return response()->json(Barang::select('id', 'nama_barang', 'harga')->get());
        $barangs = Barang::with(['barang_details' => function ($q) {
            $q->latest(); // ambil detail terakhir
        }])->get();

        $result = $barangs->map(function ($b) {
            $latestDetail = $b->barang_details->first();
            return [
                'id' => $b->id,
                'nama_barang' => $b->nama_barang,
                'harga' => $b->harga,
                'stok' => $latestDetail ? $latestDetail->total_stok_keseluruhan : 0,
            ];
        });

        return response()->json($result);
    }

    public function generateKodeBarang(){
        $lastBarang = Barang::orderBy('kode_barang', 'desc')->first();

            if ($lastBarang) {
                $lastNumber = (int) substr($lastBarang->kode_barang, 3);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $kodeBarangBaru = 'BRG' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            return response()->json([
                'kode_barang' => $kodeBarangBaru
            ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_barang' => 'required|string|max:255',
                'harga' => 'required|numeric|min:0',
                'stok' => 'required|numeric|min:0',
                'tanggal_masuk' => 'required|date',
            ], [
                'nama_barang.required' => 'nama barang wajib diisi !!',
                'nama_barang.string' => 'nama barang harus berupa teks !!',
                'nama_barang.max' => 'nama barang tidak boleh lebih dari 255 karakter !!',
                'harga.required' => 'harga wajib diisi !!',
                'harga.numeric' => 'harga harus berupa angka !!',
                'harga.min' => 'harga tidak boleh negatif !!',
                'stok.required' => 'jumlah stok wajib diisi !!',
                'stok.numeric' => 'jumlah stok harus berupa angka !!',
                'stok.min' => 'jumlah stok tidak boleh negatif !!',
                'tanggal_masuk.required' => 'tanggal masuk wajib diisi !!',
                'tanggal_masuk.date' => 'format tanggal tidak valid !!',
            ]);

            $validatedData = [
                'kode_barang' => $request->kode_barang,
                'nama_barang' => $request->nama_barang,
                'harga' => $request->harga,
            ];

            $barang = Barang::create($validatedData);

            $barang_detail = Barang::where('kode_barang', $request->kode_barang)->first();

            $barangDetail = [
                'barang_id' => $barang_detail->id,
                'stok_masuk' => $request->stok,
                'tanggal_masuk' => $request->tanggal_masuk,
                'total_stok_keseluruhan' => $request->stok,
            ];

            BarangDetail::create($barangDetail);

            return response()->json([
                'success' => true,
                'message' => 'Data barang berhasil disimpan',
                'data' => $barang
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

    public function tambahStokBaru(Request $request)
    {
        try {
            $request->validate([
                'stok_masuk' => 'required|numeric|min:0',
                'tanggal_masuk' => 'required|date',
            ], [
                'stok_masuk.required' => 'jumlah stok wajib diisi !!',
                'stok_masuk.numeric' => 'jumlah stok harus berupa angka !!',
                'stok.min' => 'jumlah stok tidak boleh negatif !!',
                'tanggal_masuk.required' => 'tanggal masuk wajib diisi !!',
                'tanggal_masuk.date' => 'format tanggal tidak valid !!',
            ]);

            $barang_id = $request->barang_id;

            $BarangDetail = BarangDetail::where('barang_id', $barang_id)
                ->orderByDesc('id')
                ->first();

            $totalStokLama =  $BarangDetail ? $BarangDetail->total_stok_keseluruhan : 0;
            $stokBaru = $request->stok_masuk;

            $total_stok_baru = $totalStokLama + $stokBaru;

            $barang = BarangDetail::create([
                'barang_id' => $barang_id,
                'stok_masuk' => $stokBaru,
                'tanggal_masuk' => $request->tanggal_masuk,
                'total_stok_keseluruhan' => $total_stok_baru,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan stok barang',
                'data' => $barang
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
        $barang = Barang::with('barang_details')->find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Data barang tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data barang ditemukan',
            'data' => $barang
        ], 200);
    }

    public function update(Request $request, $id)
    {
        // $decrypt = Crypt::decryptString($id);

        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Data barang tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'nama_barang' => 'required|string|max:40',
            'quantity' => 'required|numeric|min:0',
            'harga' => 'required|numeric|min:0',
        ], [
            'nama_barang.required' => 'nama barang wajib diisi !!',
            'nama_barang.string' => 'nama barang harus berupa teks !!',
            'nama_barang.max' => 'nama barang tidak boleh lebih dari 40 karakter !!',
            'harga.required' => 'harga wajib diisi !!',
            'harga.numeric' => 'harga harus berupa angka !!',
            'harga.min' => 'harga tidak boleh negatif !!',
            'quantity.required' => 'jumlah stok wajib diisi !!',
            'quantity.numeric' => 'jumlah stok harus berupa angka !!',
            'quantity.min' => 'jumlah stok tidak boleh negatif !!',
        ]);

        $barang->nama_barang = $request->nama_barang;
        $barang->quantity = $request->quantity;
        $barang->harga = $request->harga;
        $barang->save();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengupdate data barang',
            'data' => $barang
        ], 200);
    }

    private function resetKodeBarang()
    {
        DB::transaction(function () {
            $barangs = Barang::orderBy('kode_barang')->get();
            $no = 1;
    
            foreach ($barangs as $barang) {
                $kodeBarangBaru = 'BRG' . str_pad($no, 4, '0', STR_PAD_LEFT);
                $barang->update([
                    'kode_barang' => $kodeBarangBaru
                ]);
                $no++;
            }
        });
    }

    public function destroy($id)
    {
        try {
            $barang = Barang::findOrFail($id);

            $barang->delete();

            $this->resetKodeBarang();

            return response()->json([
                'success' => true,
                'message' => 'Data barang berhasil dihapus dan kode barang direset..',
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
