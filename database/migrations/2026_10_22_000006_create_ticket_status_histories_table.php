<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai ERD: Entitas RIWAYAT_STATUS
     */
    public function up(): void
    {
        Schema::create('ticket_status_histories', function (Blueprint $table) {
            $table->id(); // id_riwayat
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete(); // id_tiket
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // id_user
            $table->string('status_from')->nullable(); // status_sebelumnya
            $table->string('status_to'); // status_baru
            $table->text('notes')->nullable(); // keterangan
            $table->timestamps(); // created_at merepresentasikan tanggal_perubahan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_status_histories');
    }
};
