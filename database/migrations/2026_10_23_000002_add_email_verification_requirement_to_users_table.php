<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('requires_email_verification')->default(false)->after('approval_status')->index();
        });

        DB::table('users')->update(['requires_email_verification' => false]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['requires_email_verification']);
            $table->dropColumn('requires_email_verification');
        });
    }
};
