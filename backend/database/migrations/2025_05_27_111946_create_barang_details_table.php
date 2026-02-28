<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barang_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('stok_masuk')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->unsignedBigInteger('stok_keluar')->nullable();
            $table->date('tanggal_keluar')->nullable();
            $table->unsignedBigInteger('total_stok_keseluruhan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_details');
    }
};
