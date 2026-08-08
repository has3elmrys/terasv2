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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->enum('type', ['cash', 'receivable', 'payable'])->default('cash');
            $table->decimal('opening_balance', 20, 4)->default(0);   // blocker rilis pertama, data lama tidak dimigrasi
            $table->date('opening_balance_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
