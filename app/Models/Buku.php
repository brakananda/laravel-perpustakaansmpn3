<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Buku extends Model {
    protected $table = 'bukus';
    protected $fillable = [
        'kode_buku','judul_buku','pengarang','penerbit',
        'tahun_terbit','kategori_id','kelas','mata_pelajaran',
        'jumlah_buku','jumlah_tersedia'
    ];

    public function kategori() {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }
    public function peminjamans() {
        return $this->hasMany(Peminjaman::class, 'buku_id');
    }
}