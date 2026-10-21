<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_platform_admin')->default(false)->after('is_active')->index();
        });

        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 160)->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_minor');
            $table->char('currency', 3)->default('XOF');
            $table->string('billing_period', 16);
            $table->unsignedSmallInteger('billing_interval')->default(1);
            $table->json('limits')->nullable();
            $table->json('features')->nullable();
            $table->text('terms')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('status', 24)->index();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->index();
            $table->timestamp('grace_ends_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'status'], 'subscriptions_restaurant_status_index');
        });

        Schema::create('subscription_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 24)->nullable();
            $table->string('to_status', 24);
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('subscription_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3);
            $table->string('method', 32);
            $table->string('transaction_reference', 191)->nullable()->unique();
            $table->string('idempotency_key', 100)->unique();
            $table->string('status', 24)->index();
            $table->string('proof_path')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscription_status_histories');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_platform_admin');
        });
    }
};
