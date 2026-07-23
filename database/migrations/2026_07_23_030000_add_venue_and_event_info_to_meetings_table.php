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
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('venue_address')->nullable()->after('location');
            $table->string('venue_map_url')->nullable()->after('venue_address');
            $table->text('social_event_info')->nullable()->after('venue_map_url');
            $table->text('recommended_hotel_info')->nullable()->after('social_event_info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['venue_address', 'venue_map_url', 'social_event_info', 'recommended_hotel_info']);
        });
    }
};
