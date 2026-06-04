<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model {
    protected $table = 'anggotas';
    protected $fillable = ['nis','nama_siswa','kelas','jenis_kelamin','alamat','no_telp'];

    public function peminjamans() {
        return $this->hasMany(Peminjaman::class, 'anggota_id');
    }
}