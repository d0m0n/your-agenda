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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('name_kana')->nullable();
            $table->string('name_romaji')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('line_id')->nullable();
            $table->string('x_account')->nullable();
            $table->string('instagram_account')->nullable();
            $table->string('facebook_account')->nullable();
            $table->string('tiktok_account')->nullable();
            $table->string('hobby')->nullable();
            $table->string('motto')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
