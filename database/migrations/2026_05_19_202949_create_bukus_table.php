<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bukus', function (Blueprint $table) {
            $table->id();
            $table->string('kode_buku')->unique();
            $table->string('judul_buku');
            $table->string('pengarang');
            $table->string('penerbit');
            $table->year('tahun_terbit');
            $table->foreignId('kategori_id')->constrained('kategoris')->onDelete('cascade');
            $table->string('kelas'); // VII, VIII, IX
            $table->string('mata_pelajaran');
            $table->integer('jumlah_buku')->default(1);
            $table->integer('jumlah_tersedia')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('bukus');
    }
};