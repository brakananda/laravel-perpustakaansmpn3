<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model
{
    protected $table = 'pengembalians';

    protected $fillable = [
        'peminjaman_id',
        'tanggal_kembali_aktual',
        'denda',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_kembali_aktual' => 'date',
    ];

    /**
     * Relationships
     */

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function anggota()
    {
        return $this->peminjaman->anggota;
    }

    public function buku()
    {
        return $this->peminjaman->buku;
    }

    /**
     * Methods
     */

    // Format denda ke Rupiah
    public function dendaFormatted()
    {
        return 'Rp ' . number_format($this->denda, 0, ',', '.');
    }

    // Cek apakah ada denda
    public function hasFine()
    {
        return $this->denda > 0;
    }

    // Hitung keterlambatan (hari)
    public function calculateLateDays()
    {
        return $this->tanggal_kembali_aktual
                    ->diffInDays($this->peminjaman->tanggal_kembali);
    }

    // Cek apakah terlambat
    public function isLate()
    {
        return $this->tanggal_kembali_aktual
                    ->greaterThan($this->peminjaman->tanggal_kembali);
    }
}