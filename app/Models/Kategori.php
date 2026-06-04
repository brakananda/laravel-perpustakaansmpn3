<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model {
    protected $table = 'kategoris';
    protected $fillable = ['nama_kategori', 'keterangan'];

    public function bukus() {
        return $this->hasMany(Buku::class, 'kategori_id');
    }
}