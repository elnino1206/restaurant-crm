<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('restaurant_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('telegram_user_id');
            $table->string('state', 100)->default('idle');
            // partial booking data accumulated during conversation
            $table->json('payload')->nullable();
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->timestampTz('expires_at')->nullable();
            $table->timestampsTz();

            $table->unique(['restaurant_id', 'telegram_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_conversations');
    }
};