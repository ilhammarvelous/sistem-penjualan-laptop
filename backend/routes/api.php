<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\CetakTransaksiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MahasiswaMataKuliahController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\PaymentTransaksiController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PostRandomDataController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserMenuController;
use App\Http\Controllers\UserPermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/otp/generate', [OTPController::class, 'generateOTP']);
    Route::post('/otp/verifikasi-otp', [OTPController::class, 'verifikasiOTP']);
    Route::post('/otp/kirim-ulang', [OTPController::class, 'kirimUlangOTP']);
});

Route::post('/user/register', [AuthController::class, 'register']);
Route::post('/user/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/user/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('/pelanggans', PelangganController::class);
    Route::apiResource('/barangs', BarangController::class);
    Route::apiResource('/transaksis', TransaksiController::class);
    Route::apiResource('/payment-transaksi', PaymentTransaksiController::class);
    Route::apiResource('/user', UserController::class);
    Route::apiResource('/menu', MenuController::class);
    Route::get('/generate-kode-barang', [BarangController::class, 'generateKodeBarang']);
    Route::get('/generate-kode-transaksi', [TransaksiController::class, 'generateKodeIdTransaksi']);
    Route::get('/select-pelanggan', [PelangganController::class, 'select']);
    Route::get('/select-barang', [BarangController::class, 'select']);
    Route::get('/select-idTransaksi', [TransaksiController::class, 'select']);
    Route::get('/select-user', [UserController::class, 'select']);
    Route::get('/select-menu', [MenuController::class, 'select']);
    Route::post('/barangs/tambah-stok-barang', [BarangController::class, 'tambahStokBaru']);
    
    Route::get('/users-menu', [UserMenuController::class, 'getUsers']);
    Route::get('/menus/{userId}', [UserMenuController::class, 'getUserMenus']);
    Route::post('/menus/{userId}', [UserMenuController::class, 'updateMenus']);
    Route::get('/menus', [UserMenuController::class, 'getAllMenus']);
    
    Route::get('/user-permissions', [UserPermissionController::class, 'index']);
    Route::get('/user-permission/{userId}', [UserPermissionController::class, 'getUserPermissions']);
    Route::put('/user-permission-update/{userId}', [UserPermissionController::class, 'updateUserPermissions']);
    
});

Route::get('/export-pdf/{id}', [CetakTransaksiController::class, 'exportPDF']);
Route::get('/getCities', [OTPController::class, 'getCities']);
// Route::get('/random-city', [OTPController::class, 'getRandomCity']);
// Route::post('/mahasiswa/random-data', [PostRandomDataController::class, 'postRandomData']);