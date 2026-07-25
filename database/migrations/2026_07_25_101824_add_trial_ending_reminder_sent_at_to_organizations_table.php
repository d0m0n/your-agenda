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
            // トライアル終了間近メールを重複送信しないための記録
            // (SendTrialEndingRemindersコマンドが参照・更新する)。
            $table->timestamp('trial_ending_reminder_sent_at')->nullable()->after('trial_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('trial_ending_reminder_sent_at');
        });
    }
};
