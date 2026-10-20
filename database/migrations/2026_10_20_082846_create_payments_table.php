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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('original_payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('type', 24)->default('charge');
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3);
            $table->string('method', 32);
            $table->string('external_reference')->nullable();
            $table->string('idempotency_key', 100);
            $table->string('status', 24)->default('succeeded');
            $table->string('failure_reason', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('processed_at');
            $table->timestamps();

            $table->unique(['restaurant_id', 'idempotency_key']);
            $table->index(['restaurant_id', 'status', 'processed_at']);
            $table->index(['order_id', 'type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
