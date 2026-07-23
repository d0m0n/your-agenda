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
            $table->text('invitation_pdf_template')->nullable();
            $table->text('invitation_email_template')->nullable();
            $table->text('invitation_line_template')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['invitation_pdf_template', 'invitation_email_template', 'invitation_line_template']);
        });
    }
};
