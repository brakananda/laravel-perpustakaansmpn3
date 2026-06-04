<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    /**
     * Model untuk tracking denda siswa.
     * 
     * Denda bisa dari:
     * - Buku rusak/hilang: Rp 70.000 per buku
     * - Keterlambatan: Rp X per hari (dari setting deadline)
     * 
     * Status: unpaid (belum bayar) atau paid (sudah bayar)
     */
    
    protected $table = 'fines';

    protected $fillable = [
        'anggota_id',
        'buku_id',
        'peminjaman_id',
        'jenis_denda',      // rusak, hilang, terlambat
        'nominal',          // Dalam Rupiah
        'status',           // unpaid, paid
        'keterangan',
        'paid_date'
    ];

    protected $casts = [
        'paid_date' => 'date',
        'nominal' => 'decimal:2',
    ];

    /**
     * Relationships
     */

    // Siswa yang kena denda
    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    // Buku yang menjadi penyebab denda
    public function buku()
    {
        return $this->belongsTo(Buku::class);
    }

    // Transaksi peminjaman terkait
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class);
    }

    /**
     * Scopes - untuk query yang sering digunakan
     */

    // Query denda yang belum dibayar
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    // Query denda yang sudah dibayar
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Query denda rusak
    public function scopeRusak($query)
    {
        return $query->where('jenis_denda', 'rusak');
    }

    // Query denda hilang
    public function scopeHilang($query)
    {
        return $query->where('jenis_denda', 'hilang');
    }

    // Query denda terlambat
    public function scopeTerlambat($query)
    {
        return $query->where('jenis_denda', 'terlambat');
    }

    // Query denda per siswa
    public function scopeByAnggota($query, $anggota_id)
    {
        return $query->where('anggota_id', $anggota_id);
    }

    /**
     * Methods - untuk operasi atau formatting
     */

    // Format nominal ke Rupiah
    public function nominalFormatted()
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    // Status dalam Bahasa Indonesia
    public function statusLabel()
    {
        return $this->status === 'paid' ? 'Sudah Bayar' : 'Belum Bayar';
    }

    // Jenis denda dalam Bahasa Indonesia
    public function jenisLabel()
    {
        return match($this->jenis_denda) {
            'rusak' => 'Buku Rusak',
            'hilang' => 'Buku Hilang',
            'terlambat' => 'Keterlambatan',
            default => $this->jenis_denda,
        };
    }

    // Total denda unpaid untuk siswa tertentu
    public static function totalUnpaidByAnggota($anggota_id)
    {
        return self::where('anggota_id', $anggota_id)
                   ->where('status', 'unpaid')
                   ->sum('nominal');
    }
}