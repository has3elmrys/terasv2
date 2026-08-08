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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();

            $table->enum('type', ['income', 'expense', 'transfer_out', 'transfer_in']);
            $table->uuid('transfer_group_id')->nullable();

            $table->decimal('amount', 20, 4);           // currency asli, selalu positif
            $table->decimal('rate_to_base', 24, 12);    // snapshot saat transaksi
            $table->decimal('base_amount', 20, 4);      // amount × rate_to_base

            $table->date('date');
            $table->text('note')->nullable();
            $table->string('image_path')->nullable();

            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->date('occurrence_date')->nullable();

            $table->foreignId('lending_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'date']);
            $table->index(['bank_id', 'date']);
            $table->index('transfer_group_id');
            $table->index('lending_id');
            $table->unique(['subscription_id', 'occurrence_date']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
