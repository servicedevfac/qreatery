<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('late_notified_at')->nullable()->index()->after('cancelled_at');
        });

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->timestamp('expiry_notified_at')->nullable()->index()->after('cancelled_at');
        });

        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->timestamp('failure_notified_at')->nullable()->index()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->dropColumn('failure_notified_at');
        });

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropColumn('expiry_notified_at');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('late_notified_at');
        });
    }
};
