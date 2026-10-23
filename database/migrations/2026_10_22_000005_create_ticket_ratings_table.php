<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai ERD: Entitas RATING
     */
    public function up(): void
    {
        Schema::create('ticket_ratings', function (Blueprint $table) {
            $table->id(); // id_rating
            $table->foreignId('ticket_id')->unique()->constrained('tickets')->cascadeOnDelete(); // id_tiket
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // id_user (pelapor)
            $table->foreignId('padal_id')->nullable()->constrained('padals')->cascadeOnDelete(); // id_padal (entitas PADAL yang dinilai)
            $table->unsignedTinyInteger('rating'); // nilai_rating (1 - 5)
            $table->text('review')->nullable(); // komentar
            $table->timestamps(); // created_at merepresentasikan tanggal_rating
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_ratings');
    }
};
