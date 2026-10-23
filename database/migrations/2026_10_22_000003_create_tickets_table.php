<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai ERD: Entitas TIKET
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id(); // id_tiket
            $table->string('ticket_number')->unique(); // nomor_tiket
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // id_user (pelapor)
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete(); // id_kategori
            $table->foreignId('padal_id')->nullable()->constrained('padals')->nullOnDelete(); // id_padal (entitas PADAL)
            
            // Kolom pemeriksa dan penugas sesuai relasi ERD:
            // USER memeriksa(laporan) TIKET -> diperiksa_oleh
            // USER menugaskan(padal) TIKET -> ditugaskan_oleh
            $table->foreignId('diperiksa_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ditugaskan_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title'); // judul
            $table->string('location'); // lokasi
            $table->text('description'); // deskripsi
            $table->string('attachment')->nullable(); // lampiran
            
            // Status alur kerja tiket:
            // 'menunggu_verifikasi', 'ditolak_admin', 'menunggu_konfirmasi_padal', 
            // 'ditolak_padal', 'dalam_penanganan', 'selesai', 'selesai_dinilai'
            $table->string('status')->default('menunggu_verifikasi')->index();
            $table->text('rejection_reason')->nullable(); // alasan_penolakan

            $table->text('admin_notes')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->string('resolution_attachment')->nullable();
            
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable(); // tanggal_selesai
            $table->timestamps(); // created_at merepresentasikan tanggal_laporan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
