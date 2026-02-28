<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'barang_id',
        'stok_masuk',
        'tanggal_masuk',
        'stok_keluar',
        'tanggal_keluar',
        'total_stok_keseluruhan',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
