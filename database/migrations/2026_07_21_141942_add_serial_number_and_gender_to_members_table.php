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
        Schema::table('members', function (Blueprint $table) {
            $table->unsignedInteger('serial_number')->nullable()->after('position_id');
            $table->string('gender')->nullable()->after('birth_date');
            $table->unique(['organization_id', 'serial_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['organization_id', 'serial_number']);
            $table->dropColumn(['serial_number', 'gender']);
        });
    }
};
