<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            // 貼り付けた文字起こし。生成に失敗しても入力内容を失わせない
            // ためと、再生成時にフォームへ再表示するために保存する。
            $table->text('minutes_transcript')->nullable()->after('invitation_line_body');
            // 生成・手動編集後の議事録本文(invitation_*_bodyと同じ方針)。
            $table->text('minutes_body')->nullable()->after('minutes_transcript');
            $table->timestamp('minutes_generated_at')->nullable()->after('minutes_body');
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['minutes_transcript', 'minutes_body', 'minutes_generated_at']);
        });
    }
};
