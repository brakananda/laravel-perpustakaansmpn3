<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    protected $table = 'peminjamans';

    protected $fillable = [
        'kode_pinjam',
        'anggota_id',
        'buku_id',
        'tanggal_pinjam',
        'tanggal_kembali',
        'status'
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_kembali' => 'date',
    ];

    /**
     * Relationships
     */

    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class);
    }

    public function pengembalian()
    {
        return $this->hasOne(Pengembalian::class, 'peminjaman_id');
    }

    public function fines()
    {
        return $this->hasMany(Fine::class, 'peminjaman_id');
    }

    /**
     * Scopes
     */

    // Peminjaman yang masih aktif (belum dikembalikan)
    public function scopeAktif($query)
    {
        return $query->where('status', 'dipinjam');
    }

    // Peminjaman yang sudah dikembalikan
    public function scopeDikembalikan($query)
    {
        return $query->where('status', 'dikembalikan');
    }

    // Peminjaman yang terlambat
    public function scopeTerlambat($query)
    {
        return $query->where('status', 'terlambat');
    }

    // Peminjaman per siswa
    public function scopeByAnggota($query, $anggota_id)
    {
        return $query->where('anggota_id', $anggota_id);
    }

    // Peminjaman per buku
    public function scopeByBuku($query, $buku_id)
    {
        return $query->where('buku_id', $buku_id);
    }

    /**
     * Methods
     */

    // Cek apakah peminjaman masih aktif
    public function isAktif()
    {
        return $this->status === 'dipinjam';
    }

    // Hitung hari peminjaman
    public function calculateBorrowedDays()
    {
        return now()->diffInDays($this->tanggal_pinjam);
    }

    // Cek apakah terlambat
    public function isLate($compare_date = null)
    {
        $date = $compare_date ? \Carbon\Carbon::parse($compare_date) : now();
        return $date->greaterThan($this->tanggal_kembali);
    }

    // Hitung hari terlambat
    public function calculateLateDays($compare_date = null)
    {
        $date = $compare_date ? \Carbon\Carbon::parse($compare_date) : now();

        if (!$this->isLate($date)) {
            return 0;
        }

        return $date->diffInDays($this->tanggal_kembali);
    }

    // Status label
    public function statusLabel()
    {
        return match($this->status) {
            'dipinjam' => 'Sedang Dipinjam',
            'dikembalikan' => 'Sudah Dikembalikan',
            'terlambat' => 'Terlambat',
            default => $this->status,
        };
    }

        /**
     * Get class number dari buku (VII-A → VII)
     */
    public function getClassNumber()
    {
        return $this->buku->getClassNumber();
    }

    /**
     * Hitung total denda untuk peminjaman ini
     */
    public function getTotalFines()
    {
        return $this->fines()->where('status', 'unpaid')->sum('nominal');
    }

    /**
     * Get semua fine records untuk peminjaman ini
     */
    public function getFineSummary()
    {
        return $this->fines()
                    ->selectRaw('jenis_denda, SUM(nominal) as total, COUNT(*) as jumlah')
                    ->groupBy('jenis_denda')
                    ->get();
    }
}