<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel untuk setting deadline pengembalian massal per tahun ajaran.
     * Contoh: Deadline 15 Juni 2026 untuk TA 2025/2026
     * 
     * Hanya bisa 1 deadline yang active (is_active = true) sekaligus
     * Deadline lama tetap disimpan untuk referensi/laporan
     */
    public function up(): void
    {
        Schema::create('return_deadlines', function (Blueprint $table) {
            $table->id();
            
            // Tanggal batas pengembalian massal
            $table->date('deadline_date');
            
            // Catatan/himbauan dari sekolah
            $table->text('note')->nullable();
            
            // Apakah deadline ini sedang berlaku?
            // Hanya 1 yang bisa true sekaligus
            $table->boolean('is_active')
                  ->default(false)
                  ->index(); // Index untuk query deadline aktif
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_deadlines');
    }
};