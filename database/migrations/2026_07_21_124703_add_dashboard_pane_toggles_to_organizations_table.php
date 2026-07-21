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
        Schema::table('organizations', function (Blueprint $table) {
            $table->boolean('show_meetings_pane')->default(true);
            $table->boolean('show_calendar_pane')->default(true);
            $table->boolean('show_birthday_pane')->default(true);
            $table->boolean('show_materials_pane')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'show_meetings_pane', 'show_calendar_pane', 'show_birthday_pane', 'show_materials_pane',
            ]);
        });
    }
};
