<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model {
    protected $table = 'pengembalians';
    protected $fillable = ['peminjaman_id','tanggal_kembali_aktual','denda','keterangan'];

    public function peminjaman() { return $this->belongsTo(Peminjaman::class, 'peminjaman_id'); }
}