<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel untuk tracking denda secara terpisah:
     * - Denda rusak/hilang: Rp 70.000 per buku
     * - Denda terlambat: Rp X per hari (calculated)
     * - Status: unpaid (belum bayar) atau paid (sudah bayar)
     */
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('anggota_id')
                  ->constrained('anggotas')
                  ->onDelete('cascade');
            
            $table->foreignId('buku_id')
                  ->constrained('bukus')
                  ->onDelete('cascade');
            
            $table->foreignId('peminjaman_id')
                  ->constrained('peminjamans')
                  ->onDelete('cascade');
            
            // Jenis denda: rusak, hilang, atau terlambat
            $table->enum('jenis_denda', ['rusak', 'hilang', 'terlambat'])
                  ->index(); // Add index untuk query lebih cepat
            
            // Nominal denda (dalam Rupiah)
            $table->decimal('nominal', 10, 2);
            
            // Status pembayaran
            $table->enum('status', ['unpaid', 'paid'])
                  ->default('unpaid')
                  ->index(); // Add index untuk query unpaid denda
            
            // Keterangan (alasan rusak, hilang, dsb)
            $table->text('keterangan')->nullable();
            
            // Tanggal dibayar
            $table->date('paid_date')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};