<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai ERD: Entitas PADAL dan PADAL_ANGGOTA
     */
    public function up(): void
    {
        // 1. Entitas PADAL (Perwira Pengendali / Regu Padal)
        Schema::create('padals', function (Blueprint $table) {
            $table->id(); // id_padal pada ERD
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // id_user (Ketua / Pemimpin Padal)
            $table->string('nama_padal'); // nama_padal pada ERD
            $table->text('deskripsi')->nullable(); // deskripsi pada ERD
            $table->string('status')->default('aktif'); // status pada ERD ('aktif'/'nonaktif')
            $table->timestamps();
        });

        // 2. Entitas PADAL_ANGGOTA
        Schema::create('padal_anggota', function (Blueprint $table) {
            $table->id(); // id_padal_anggota pada ERD
            $table->foreignId('padal_id')->constrained('padals')->cascadeOnDelete(); // id_padal pada ERD
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // id_user (Anggota Teknisi) pada ERD
            $table->timestamp('tanggal_bergabung')->useCurrent(); // tanggal_bergabung pada ERD
            $table->timestamps();

            // 1 teknisi tidak duplikat di padal yang sama
            $table->unique(['padal_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('padal_anggota');
        Schema::dropIfExists('padals');
    }
};
