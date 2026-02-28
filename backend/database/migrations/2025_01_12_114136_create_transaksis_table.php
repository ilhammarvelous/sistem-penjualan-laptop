<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('id_transaksi')->unique()->nullable();
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->date('tanggal')->nullable();
            $table->unsignedBigInteger('total_pembayaran')->nullable();
            $table->enum('status', ['Belum Lunas', 'Lunas']);
            $table->timestamps();
            
            $table->foreign('pelanggan_id')->references('id')->on('pelanggans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
