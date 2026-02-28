<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class CetakTransaksiController extends Controller
{
    public function exportPDF($id){

        $decrypt = Crypt::decrypt($id);

        $transaksi = DB::table('transaksis')
        ->join('pelanggans', 'pelanggans.id', '=', 'transaksis.pelanggan_id')
        ->select(
            'transaksis.id',
            'transaksis.id_transaksi',
            'transaksis.tanggal',
            'transaksis.total_pembayaran',
            'pelanggans.nama as nama',
            'pelanggans.alamat as alamat',
            'transaksis.status'
        )
        ->where('transaksis.id', $decrypt)
        ->first();

        $detail = DB::table('detail_transaksi')
            ->join('barangs', 'barangs.id', '=', 'detail_transaksi.barang_id')
            ->select(
                'barangs.nama_barang as nama_barang',
                'detail_transaksi.harga',
                'detail_transaksi.quantity',
                'detail_transaksi.subtotal',
                'detail_transaksi.barang_id'
            )
            ->where('detail_transaksi.transaksis_id', $decrypt)
            ->get();

        $pdf = PDF::loadView('invoice.export', compact('transaksi', 'detail'))->setPaper('A4', 'portrait');
        return $pdf->stream('invoice_' . $transaksi->id_transaksi . '.pdf');
    }
}
