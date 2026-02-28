<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('transaksis')
            ->join('pelanggans', 'transaksis.pelanggan_id', '=', 'pelanggans.id')
            ->select(
                'transaksis.id',
                'transaksis.id_transaksi',
                'pelanggans.nama as pelanggan',
                'transaksis.tanggal',
                'transaksis.total_pembayaran',
                'transaksis.status'
            )
            ->orderBy('transaksis.id_transaksi');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('id_transaksi', 'LIKE', "%{$search}%")
                ->orWhere('pelanggans.nama', 'LIKE', "%{$search}%")
                ->orWhere('tanggal', 'LIKE', "%{$search}%")
                ->orWhere('status', 'LIKE', "%{$search}%");
        }

        $data = $query->paginate(10);

        $data->getCollection()->transform(function ($item) {
            $item->id = Crypt::encrypt($item->id);
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Data ditemukan',
            'data' => $data
        ]);
    }

    public function select()
    {
        $data = DB::table('transaksis')
            ->select('id', 'id_transaksi', 'total_pembayaran')
            ->get();

        return response()->json($data);
    }

    public function generateKodeIdTransaksi()
    {
        $prefix = 'OTWCG';
        $now = Carbon::now();
        $kodeBulanTahun = $now->format('my');

        $lastTransaksi = DB::table('transaksis')
            ->orderBy('id_transaksi', 'desc')
            ->first();

        if ($lastTransaksi) {
            $lastId = $lastTransaksi->id_transaksi;

            $prefixLength = strlen($prefix);
            $bulanTahun = substr($lastId, $prefixLength, 4);

            $nomorUrut = substr($lastId, $prefixLength + 4);

            if ($bulanTahun == $kodeBulanTahun) {
                $lastNumber = (int) $nomorUrut;
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
        } else {
            $newNumber = 1;
        }

        $kodeBaru = $prefix . $kodeBulanTahun . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

        return response()->json([
            'id_transaksi' => $kodeBaru
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_transaksi' => 'required|unique:transaksis,id_transaksi',
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required',
            'items.*.harga' => 'required|numeric',
            'items.*.quantity' => 'required|numeric|min:1',
            'total_pembayaran' => 'nullable|integer',
        ], [
            'pelanggan_id.required' => 'Pelanggan wajib diisi !!',

            'tanggal.required' => 'Tanggal wajib diisi !!',
            'tanggal.date' => 'Format tanggal tidak valid !!',
            'items.*.barang_id.required' => 'Barang wajib diisi !!',
            'items.*.harga.required' => 'Harga wajib diisi untuk setiap item !!',
            'items.*.harga.integer' => 'Harga harus berupa angka bulat untuk setiap item !!',
            'items.*.harga.min' => 'Harga tidak boleh bernilai negatif!',
            'items.*.quantity.required' => 'Quantity wajib diisi untuk setiap item!',
            'items.*.quantity.integer' => 'Quantity harus berupa angka bulat untuk setiap item!',
            'items.*.quantity.min' => 'Quantity tidak boleh bernilai negatif!',
            'total_pembayaran.nullable' => 'Total pembayaran boleh kosong !!',
            'total_pembayaran.integer' => 'Total pembayaran harus berupa angka bulat !!',
        ]);

        DB::beginTransaction();

        try {
            // 🔍 Validasi stok cukup untuk tiap barang
            foreach ($validatedData['items'] as $item) {
                $latestDetail = DB::table('barang_details')
                    ->where('barang_id', $item['barang_id'])
                    ->orderByDesc('id')
                    ->first();

                $currentTotalStok = $latestDetail->total_stok_keseluruhan ?? 0;

                if ($item['quantity'] > $currentTotalStok) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok tidak mencukupi untuk barang ID: ' . $item['barang_id'],
                    ], 400);
                }
            }

            // 📝 Simpan transaksi utama
            $transaksiId = DB::table('transaksis')->insertGetId([
                'id_transaksi' => $validatedData['id_transaksi'],
                'pelanggan_id' => $validatedData['pelanggan_id'],
                'tanggal' => $validatedData['tanggal'],
                'total_pembayaran' => $validatedData['total_pembayaran'] ?? 0,
                'created_at' => $validatedData['tanggal'],
                'updated_at' => $validatedData['tanggal'],
            ]);

            // 🔄 Proses detail transaksi dan update stok
            foreach ($validatedData['items'] as $item) {
                $subtotal = $item['harga'] * $item['quantity'];

                DB::table('detail_transaksi')->insert([
                    'transaksis_id' => $transaksiId,
                    'barang_id' => $item['barang_id'],
                    'harga' => $item['harga'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // ✅ Ambil stok terakhir
                $latest = DB::table('barang_details')
                    ->where('barang_id', $item['barang_id'])
                    ->orderByDesc('id')
                    ->first();

                $stokSebelumnya = $latest->total_stok_keseluruhan ?? 0;
                $newTotal =  $stokSebelumnya - $item['quantity'];

                // if ($newTotal < 0) {
                //     throw new \Exception('Stok tidak mencukupi saat mengurangi stok barang_id: ' . $item['barang_id']);
                // }

                // 🆕 Insert baris baru stok keluar
                DB::table('barang_details')->insert([
                    'barang_id' => $item['barang_id'],
                    'stok_masuk' => null,
                    'stok_keluar' => $item['quantity'],
                    'total_stok_keseluruhan' => $newTotal,
                    'tanggal_keluar' => $validatedData['tanggal'],
                    'created_at' => $validatedData['tanggal'],
                    'updated_at' => $validatedData['tanggal'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data Transaksi berhasil ditambahkan'
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi',
                'error' => $e->getMessage(),
            ], 500);
        }


        // DB::beginTransaction();

        // try {
        //      // 🔍 Validasi stok cukup untuk tiap barang
        //     foreach ($validatedData['items'] as $item) {
        //         $latestDetail = DB::table('barang_details')
        //             ->where('barang_id', $item['barang_id'])
        //             ->latest('created_at')
        //             ->first();

        //         $currentTotalStok = $latestDetail->total_stok_keseluruhan ?? 0;

        //         if ($item['quantity'] > $currentTotalStok) {
        //             return response()->json([
        //                 'success' => false,
        //                 'message' => 'Stok tidak mencukupi untuk barang ID: ' . $item['barang_id'],
        //             ], 400);
        //         }
        //     }

        //     // 📝 Simpan transaksi utama
        //     $transaksiId = DB::table('transaksis')->insertGetId([
        //         'id_transaksi' => $validatedData['id_transaksi'],
        //         'pelanggan_id' => $validatedData['pelanggan_id'],
        //         'tanggal' => $validatedData['tanggal'],
        //         'total_pembayaran' => $validatedData['total_pembayaran'] ?? 0,
        //         'created_at' => $validatedData['tanggal'],
        //         'updated_at' => now(),
        //     ]);

        //     // 🔄 Proses detail transaksi dan update stok
        //     foreach ($validatedData['items'] as $item) {
        //         $subtotal = $item['harga'] * $item['quantity'];

        //         DB::table('detail_transaksi')->insert([
        //             'transaksis_id' => $transaksiId,
        //             'barang_id' => $item['barang_id'],
        //             'harga' => $item['harga'],
        //             'quantity' => $item['quantity'],
        //             'subtotal' => $subtotal,
        //             'created_at' => now(),
        //             'updated_at' => now(),
        //         ]);

        //         $qtyToDeduct = $item['quantity'];

        //         $existingRow = DB::table('barang_details')
        //             ->where('barang_id', $item['barang_id'])
        //             ->whereNull('stok_keluar')
        //             ->whereNull('tanggal_keluar')
        //             ->whereNull('stok_masuk') 
        //             ->oldest('created_at')
        //             ->first();

        //         if ($existingRow) {
        //             $newStokKeluar = $qtyToDeduct;
        //             $newTotal = $existingRow->total_stok_keseluruhan - $newStokKeluar;

        //             if ($newTotal < 0) {
        //                 throw new \Exception('Stok tidak mencukupi saat update barang_id: ' . $item['barang_id']);
        //             }

        //             DB::table('barang_details')
        //                 ->where('id', $existingRow->id)
        //                 ->update([
        //                     'stok_keluar' => $newStokKeluar,
        //                     'total_stok_keseluruhan' => $newTotal,
        //                     'tanggal_keluar' => $validatedData['tanggal'],
        //                     'created_at' => $validatedData['tanggal'],
        //                     'updated_at' => $validatedData['tanggal'],
        //                 ]);
        //         } else {
        //             $latest = DB::table('barang_details')
        //                 ->where('barang_id', $item['barang_id'])
        //                 ->latest('created_at')
        //                 ->first();
                    
        //             $latestTotal = $latest->total_stok_keseluruhan ?? 0;

        //             if ($qtyToDeduct > $latestTotal) {
        //                 throw new \Exception('Stok tidak mencukupi saat menambahkan baris baru barang_id: ' . $item['barang_id']);
        //             }

        //             DB::table('barang_details')->insert([
        //                 'barang_id' => $item['barang_id'],
        //                 'stok_masuk' => null,
        //                 'stok_keluar' => $qtyToDeduct,
        //                 'total_stok_keseluruhan' => $latestTotal - $qtyToDeduct,
        //                 'tanggal_keluar' => $validatedData['tanggal'],
        //                 'created_at' => $validatedData['tanggal'],
        //                 'updated_at' => $validatedData['tanggal'],
        //             ]);
        //         }

        //          // Ambil baris terakhir berdasarkan waktu masuk
        //         // $latestDetail = DB::table('barang_details')
        //         //     ->where('barang_id', $item['barang_id'])
        //         //     ->latest('created_at')
        //         //     ->first();

        //         // $stokKeluarBaru = ($latestDetail->stok_keluar ?? 0) + $item['quantity'];
        //         // $stokTotalBaru = $latestDetail->total_stok_keseluruhan - $item['quantity'];

        //         // if ($stokTotalBaru < 0) {
        //         //     throw new \Exception('Stok tidak mencukupi saat update barang_id: ' . $item['barang_id']);
        //         // }

        //         // Update baris terakhir saja
        //         // DB::table('barang_details')
        //         //     ->where('id', $latestDetail->id)
        //         //     ->update([
        //         //         'stok_keluar' => $stokKeluarBaru,
        //         //         'total_stok_keseluruhan' => $stokTotalBaru,
        //         //         'tanggal_keluar' => now(),
        //         //         'updated_at' => now(),
        //         //     ]);

        //         // DB::table('barang_details')
        //         //     ->where('barang_id', $item['barang_id'])
        //         //     ->orderBy('id')
        //         //     ->limit(1) // atau sesuaikan pengurangan berdasarkan batch
        //         //     ->decrement('total_stok_keseluruhan', $item['quantity']);
        //     }

        //     DB::commit();

        //     return response()->json([
        //         'success' => true,
        //         'message' => 'Data Transaksi berhasil ditambahkan'
        //     ], 201);
        // } catch (\Exception $e) {
        //     DB::rollback();

        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Gagal menyimpan transaksi',
        //         'error' => $e->getMessage(),
        //     ], 500);
        // }
    }

    public function show($id)
    {
        $decrypt = Crypt::decrypt($id);

        $transaksi = DB::table('transaksis')
            ->join('pelanggans', 'pelanggans.id', '=', 'transaksis.pelanggan_id')
            ->select(
                'transaksis.id',
                'transaksis.id_transaksi',
                'transaksis.tanggal',
                'transaksis.total_pembayaran',
                'pelanggans.nama as pelanggan',
                'transaksis.status'
            )
            ->where('transaksis.id', $decrypt)
            ->first();

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'data transaksi tidak ditemukan'
            ], 404);
        }

        $detail = DB::table('detail_transaksi')
            ->join('barangs', 'barangs.id', '=', 'detail_transaksi.barang_id')
            ->select(
                'barangs.nama_barang as barang',
                'detail_transaksi.harga',
                'detail_transaksi.quantity',
                'detail_transaksi.subtotal',
                'detail_transaksi.barang_id'
            )
            ->where('detail_transaksi.transaksis_id', $decrypt)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'transaksi' => $transaksi,
                'detail' => $detail
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $decrypt = Crypt::decrypt($id);

        $validatedData = $request->validate([
            'pelanggan_id' => 'exists:pelanggans,id',
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.harga' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:1',
            'total_pembayaran' => 'nullable|integer',
        ]);

        $transaksi = DB::table('transaksis')->where('id', $decrypt)->first();

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Data transaksi tidak ditemukan!'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Ambil semua detail lama dan kembalikan stoknya dulu
            $detailLama = DB::table('detail_transaksi')->where('transaksis_id', $decrypt)->get();
            foreach ($detailLama as $detail) {
                DB::table('barangs')
                    ->where('id', $detail->barang_id)
                    ->increment('quantity', $detail->quantity);
            }

            // Hapus semua detail lama
            DB::table('detail_transaksi')->where('transaksis_id', $decrypt)->delete();

            // Update transaksi utama
            DB::table('transaksis')->where('id', $decrypt)->update([
                'pelanggan_id' => $validatedData['pelanggan_id'],
                'tanggal' => $validatedData['tanggal'],
                'total_pembayaran' => $validatedData['total_pembayaran'] ?? 0,
                'updated_at' => now(),
            ]);

            // Insert ulang detail transaksi
            foreach ($validatedData['items'] as $item) {
                $subtotal = $item['harga'] * $item['quantity'];

                DB::table('detail_transaksi')->insert([
                    'transaksis_id' => $decrypt,
                    'barang_id' => $item['barang_id'],
                    'harga' => $item['harga'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Kurangi stok
                DB::table('barangs')
                    ->where('id', $item['barang_id'])
                    ->decrement('quantity', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data transaksi berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data transaksi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function resetKodeTransaksi()
    {
        DB::transaction(function () {
        $prefix = 'OTWCG';
        $now = Carbon::now();
        $kodeBulanTahun = $now->format('my'); // Bulan dan Tahun saat ini, misalnya "0525"
        $no = 1;

        // Ambil semua transaksi dan urutkan berdasarkan created_at atau id
        $transaksis = DB::table('transaksis')->orderBy('created_at')->get();

        foreach ($transaksis as $transaksi) {
            $kodeBaru = $prefix . $kodeBulanTahun . str_pad($no, 3, '0', STR_PAD_LEFT);

            // Update ke database
            DB::table('transaksis')
                ->where('id', $transaksi->id)
                ->update([
                    'id_transaksi' => $kodeBaru
                ]);

            $no++;
        }
    });
    }

    public function destroy($id)
    {
        try {
            $decrypt = Crypt::decrypt($id);

            DB::table('transaksis')->where('id', $decrypt)->delete();

            $this->resetKodeTransaksi();

            return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil dihapus!'
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
