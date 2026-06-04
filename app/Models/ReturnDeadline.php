<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnDeadline extends Model
{
    /**
     * Model untuk setting deadline pengembalian massal per tahun ajaran.
     * 
     * Contoh:
     * - Deadline 15 Juni 2026 untuk TA 2025/2026
     * - Hanya 1 deadline yang bisa is_active = true sekaligus
     * - Deadline lama disimpan untuk referensi/laporan
     */
    
    protected $table = 'return_deadlines';

    protected $fillable = [
        'deadline_date',
        'note',
        'is_active',
    ];

    protected $casts = [
        'deadline_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */

    // Peminjaman yang terkena deadline ini
    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class, 'deadline_id');
    }

    /**
     * Scopes
     */

    // Query deadline yang sedang aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Query deadline yang tidak aktif (archive)
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Methods
     */

    // Dapatkan deadline yang aktif saat ini
    public static function getActiveDeadline()
    {
        return self::where('is_active', true)->first();
    }

    // Cek apakah ada deadline aktif
    public static function hasActiveDeadline()
    {
        return self::where('is_active', true)->exists();
    }

    // Hitung hari terlambat dari deadline ini
    public function calculateLateDays($compare_date = null)
    {
        $date = $compare_date ? \Carbon\Carbon::parse($compare_date) : now();
        
        if ($date->lessThanOrEqualTo($this->deadline_date)) {
            return 0; // Tidak terlambat
        }

        return $date->diffInDays($this->deadline_date);
    }

    // Format tanggal deadline ke format Indonesia
    public function formattedDate()
    {
        return $this->deadline_date->format('d F Y');
    }

    // Set sebagai deadline aktif (dan nonaktifkan yang lain)
    public function setAsActive()
    {
        // Nonaktifkan semua deadline lama
        self::where('id', '!=', $this->id)
             ->update(['is_active' => false]);

        // Aktifkan yang sekarang
        $this->update(['is_active' => true]);
    }
}