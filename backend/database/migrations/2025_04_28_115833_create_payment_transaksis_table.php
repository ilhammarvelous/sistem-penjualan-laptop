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
        Schema::create('payment_transaksis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaksis_id');
            $table->unsignedBigInteger('total_pembayaran')->nullable();
            $table->unsignedBigInteger('pembayaran')->nullable();
            $table->string('via')->nullable();
            $table->date('tanggal')->nullable();
            $table->timestamps();

            $table->foreign('transaksis_id')->references('id')->on('transaksis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transaksis');
    }
};
