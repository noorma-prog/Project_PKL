<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->date('event_date')->nullable()->after('location');
            $table->time('event_start_time')->nullable()->after('event_date');
            $table->time('event_end_time')->nullable()->after('event_start_time');
            $table->index(['event_date', 'event_start_time'], 'tickets_event_schedule_index');
            $table->index(['created_at', 'id'], 'tickets_submission_order_index');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_event_schedule_index');
            $table->dropIndex('tickets_submission_order_index');
            $table->dropColumn(['event_date', 'event_start_time', 'event_end_time']);
        });
    }
};
