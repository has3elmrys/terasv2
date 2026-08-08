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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('bank_id')->constrained()->restrictOnDelete();
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();

            $table->string('name');
            $table->decimal('amount', 20, 4);
            $table->decimal('rate_to_base', 24, 12)->nullable();   // null = pakai rate hari-H saat generate
            $table->string('receipt_template')->nullable();
            $table->text('description')->nullable();

            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->unsignedInteger('interval')->default(1);        // "periode ulang"
            $table->date('start_date');
            $table->date('next_date');
            $table->enum('status', ['active', 'paused', 'cancelled'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['next_date', 'status']);
            $table->index(['team_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
