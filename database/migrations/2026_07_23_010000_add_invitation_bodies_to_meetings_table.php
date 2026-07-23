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
            $table->text('invitation_pdf_body')->nullable()->after('public_token');
            $table->text('invitation_email_body')->nullable()->after('invitation_pdf_body');
            $table->text('invitation_line_body')->nullable()->after('invitation_email_body');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['invitation_pdf_body', 'invitation_email_body', 'invitation_line_body']);
        });
    }
};
