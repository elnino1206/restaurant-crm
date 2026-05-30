<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('restaurant_id')->nullable()->constrained()->nullOnDelete();
            // morphable — User, Customer, etc.
            $table->string('notifiable_type', 100);
            $table->uuid('notifiable_id');
            // telegram | email | sms
            $table->string('channel', 50);
            $table->string('notification_type', 200);
            $table->json('data')->nullable();
            $table->timestampTz('sent_at')->nullable();
            // pending | sent | failed
            $table->string('status', 50)->default('pending');
            $table->text('error')->nullable();
            $table->timestampsTz();

            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index(['restaurant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};