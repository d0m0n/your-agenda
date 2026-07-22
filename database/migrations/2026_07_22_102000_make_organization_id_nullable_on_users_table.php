<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * super_admin アカウントはどの組織にも属さないため、users.organization_id を
     * nullable にする。doctrine/dbal を追加せずに済むよう生SQLで変更する。
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE users MODIFY organization_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY organization_id BIGINT UNSIGNED NOT NULL');
    }
};
