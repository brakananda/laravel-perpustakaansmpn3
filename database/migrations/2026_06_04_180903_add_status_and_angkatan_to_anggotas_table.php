<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan 2 kolom baru ke tabel anggotas:
     * - status: Aktif, Alumni, atau Keluar
     * - angkatan: Tahun siswa masuk (2022, 2023, 2024)
     */
    public function up(): void
    {
        Schema::table('anggotas', function (Blueprint $table) {
            // Tambah kolom status (enum: Aktif, Alumni, Keluar)
            $table->enum('status', ['Aktif', 'Alumni', 'Keluar'])
                  ->default('Aktif')
                  ->after('no_telp'); // Letakkan setelah no_telp

            // Tambah kolom angkatan (tahun, integer)
            $table->integer('angkatan')
                  ->nullable()
                  ->after('status'); // Letakkan setelah status
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anggotas', function (Blueprint $table) {
            // Drop kolom jika rollback
            $table->dropColumn(['status', 'angkatan']);
        });
    }
};