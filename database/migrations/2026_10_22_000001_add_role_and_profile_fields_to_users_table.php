<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password')->index(); // 'user', 'admin', 'padal', 'teknisi'
            $table->string('nrp_nip')->nullable()->after('role'); // NRP/NIP personel POLDA / pelapor
            $table->string('phone_number')->nullable()->after('nrp_nip');
            $table->text('alamat')->nullable()->after('phone_number'); // Alamat domisili/kantor pelapor (ERD)
            $table->string('satuan_kerja')->nullable()->after('alamat'); // Satker: Ditlantas, Bid TIK, SPKT, dll.
            $table->boolean('is_active')->default(true)->after('satuan_kerja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'nrp_nip', 'phone_number', 'alamat', 'satuan_kerja', 'is_active']);
        });
    }
};
