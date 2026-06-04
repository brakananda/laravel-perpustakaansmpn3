<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    protected $table = 'anggotas';

    protected $fillable = [
        'nis',
        'nama_siswa',
        'kelas',
        'jenis_kelamin',
        'alamat',
        'no_telp',
        'status',      // NEW: Aktif, Alumni, Keluar
        'angkatan',    // NEW: 2022, 2023, 2024
    ];

    /**
     * Relationships
     */

    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class, 'anggota_id');
    }

    public function fines()
    {
        return $this->hasMany(Fine::class, 'anggota_id');
    }

    /**
     * Scopes - Query helpers
     */

    // Hanya siswa yang aktif
    public function scopeAktif($query)
    {
        return $query->where('status', 'Aktif');
    }

    // Hanya siswa alumni
    public function scopeAlumni($query)
    {
        return $query->where('status', 'Alumni');
    }

    // Hanya siswa yang keluar
    public function scopeKeluar($query)
    {
        return $query->where('status', 'Keluar');
    }

    // Filter per angkatan
    public function scopeByAngkatan($query, $angkatan)
    {
        return $query->where('angkatan', $angkatan);
    }

    // Filter per kelas
    public function scopeByKelas($query, $kelas)
    {
        return $query->where('kelas', $kelas);
    }

    /**
     * Methods - Custom functionality
     */

    // Cek apakah siswa status Aktif
    public function isAktif()
    {
        return $this->status === 'Aktif';
    }

    // Cek apakah siswa bisa meminjam
    public function canBorrow()
    {
        return $this->isAktif();
    }

    // Cek apakah siswa alumni
    public function isAlumni()
    {
        return $this->status === 'Alumni';
    }

    // Hitung total denda unpaid siswa ini
    public function getTotalUnpaidFines()
    {
        return $this->fines()
                    ->where('status', 'unpaid')
                    ->sum('nominal');
    }

    // Hitung total buku yang sedang dipinjam
    public function getTotalBorrowedBooks()
    {
        return $this->peminjamans()
                    ->where('status', 'dipinjam')
                    ->count();
    }

    // Dapatkan list buku yang sedang dipinjam
    public function getBorrowedBooks()
    {
        return $this->peminjamans()
                    ->where('status', 'dipinjam')
                    ->with('buku')
                    ->get();
    }

    // Format kelas (VII-A → VII)
    public function getClassNumber()
    {
        return explode('-', $this->kelas)[0];
    }

    // Status label dalam Bahasa Indonesia
    public function statusLabel()
    {
        return match($this->status) {
            'Aktif' => 'Aktif',
            'Alumni' => 'Alumni',
            'Keluar' => 'Keluar',
            default => $this->status,
        };
    }
}