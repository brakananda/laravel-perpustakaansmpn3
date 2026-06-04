<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder {
    public function run(): void {
        DB::table('kategoris')->insert([
            ['nama_kategori' => 'K13',     'keterangan' => 'Buku Kurikulum 2013',        'created_at' => now(), 'updated_at' => now()],
            ['nama_kategori' => 'Merdeka', 'keterangan' => 'Buku Kurikulum Merdeka Belajar', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}