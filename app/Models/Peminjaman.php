<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model {
    protected $table = 'peminjamans';
    protected $fillable = [
        'kode_pinjam','anggota_id','buku_id',
        'tanggal_pinjam','tanggal_kembali','status'
    ];

    // public function anggota() { return $this->belongsTo(Anggota::class, 'anggota_id'); }
    public function anggota()
{
    return $this->belongsTo(Anggota::class);
}
public function buku()
{
    return $this->belongsTo(Buku::class);
}
    // public function buku() { return $this->belongsTo(Buku::class,    'buku_id'); }
    public function scopeAktif($query) { return $query->where('status','dipinjam'); }
    public function pengembalian() { return $this->hasOne(Pengembalian::class, 'peminjaman_id'); }
}