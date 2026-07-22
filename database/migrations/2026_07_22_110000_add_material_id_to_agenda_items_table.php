<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 議案データのリンク先として、会議専用のsites(議案ファイル)だけでなく、
     * 組織内で共有されているmaterials(資料置き場)も選べるようにする。
     * site_id/material_idは常にどちらか一方のみが入る(排他)。
     */
    public function up(): void
    {
        Schema::table('agenda_items', function (Blueprint $table) {
            $table->foreignId('material_id')->nullable()->after('site_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agenda_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('material_id');
        });
    }
};
