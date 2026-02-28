<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentTransaksiController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'transaksis_id' => 'required|exists:transaksis,id',
            'tanggal' => 'required|date',
            'via' => 'required|string',
            'total_pembayaran' => 'required|numeric',
            'pembayaran' => 'required|numeric',
        ], [
            'transaksis_id.required' => 'ID transaksi wajib dipilih !!',
            'transaksis_id.exists' => 'ID transaksi tidak ditemukan di database !!',
            'tanggal.required' => 'Tanggal wajib diisi !!',
            'tanggal.date' => 'Format tanggal tidak valid !!',
            'via.required' => 'Via pembayaran wajib diisi !!',
            'via.string' => 'Via pembayaran harus berupa teks !!',
            'total_pembayaran.required' => 'Total pembayaran wajib diisi !!',
            'total_pembayaran.numeric' => 'Total pembayaran harus berupa angka !!',
            'pembayaran.required' => 'Nominal pembayaran wajib diisi !!',
            'pembayaran.numeric' => 'Nominal pembayaran harus berupa angka !!',
        ]);

        DB::beginTransaction();

        try {
            DB::table('payment_transaksis')->insert([
                'transaksis_id' => $validatedData['transaksis_id'],
                'tanggal' => $validatedData['tanggal'],
                'via' => $validatedData['via'],
                'total_pembayaran' => $validatedData['total_pembayaran'],
                'pembayaran' => $validatedData['pembayaran'],
                'created_at' => $validatedData['tanggal'],
            ]);

            DB::table('transaksis')
                ->where('id', $validatedData['transaksis_id'])
                ->update(['status' => 'Lunas']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil melakukan pembayaran transaksi'
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pembayaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
