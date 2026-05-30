<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->constrained('billing_subscriptions')->cascadeOnDelete();
            // amount in cents
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('USD');
            // pending | completed | failed | refunded
            $table->string('status', 50);
            $table->string('gateway', 50);
            $table->string('gateway_transaction_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestampTz('processed_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_transactions');
    }
};