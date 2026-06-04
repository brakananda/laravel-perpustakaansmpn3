<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('anggotas', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique();
            $table->string('nama_siswa');
            $table->string('kelas'); // VII-A, VIII-B, dst
            $table->string('jenis_kelamin')->default('L'); // L/P
            $table->string('alamat')->nullable();
            $table->string('no_telp')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('anggotas');
    }
};