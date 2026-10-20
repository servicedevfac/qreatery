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
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('label', 80);
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(['restaurant_id', 'label']);
            $table->index(['restaurant_id', 'is_active']);
        });

        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_table_id')->nullable()->constrained()->nullOnDelete();
            $table->string('token', 64)->unique();
            $table->string('type', 24);
            $table->string('status', 24)->default('active');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'type', 'status']);
            $table->index(['restaurant_table_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
        Schema::dropIfExists('restaurant_tables');
    }
};
