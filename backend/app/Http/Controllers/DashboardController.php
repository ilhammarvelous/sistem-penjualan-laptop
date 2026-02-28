<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $tahun = request('tahun', now()->year);
        $bulan = Carbon::now()->month;

        Carbon::setLocale('id');
        $bulanFormat = Carbon::now()->translatedFormat('F');

        $jumlah_lunas = DB::table('transaksis')
            ->whereYear('created_at', $tahun)
            ->where('status', 'Lunas')
            ->count();

        $jumlah_belum_lunas = DB::table('transaksis')
            ->whereYear('created_at', $tahun)
            ->where('status', 'Belum Lunas')
            ->count();

        $pendapatan_tahun = DB::table('transaksis')
            ->whereYear('created_at', $tahun)
            ->where('status', 'Lunas')
            ->sum('total_pembayaran');

        $pendapatan_bulan = DB::table('transaksis')
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->where('status', 'Lunas')
            ->sum('total_pembayaran');

        $jumlah_pelanggan = DB::table('pelanggans')->count();
        $jumlah_barang = DB::table('barangs')->count();

        $grafik_tahunan = DB::table('transaksis')
            ->select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('SUM(total_pembayaran) as total')
            )
            ->whereYear('created_at', $tahun)
            ->where('status', 'Lunas')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        return response()->json([
            'bulan' => $bulanFormat,
            'jumlah_lunas' => $jumlah_lunas,
            'jumlah_belum_lunas' => $jumlah_belum_lunas,
            'pendapatan_tahun' => $pendapatan_tahun,
            'pendapatan_bulan' => $pendapatan_bulan,
            'jumlah_pelanggan' => $jumlah_pelanggan,
            'jumlah_barang' => $jumlah_barang,
            'grafik_tahunan' => $grafik_tahunan
        ]);
    }
}
