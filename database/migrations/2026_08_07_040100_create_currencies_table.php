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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();                        // "USD", "IDR"
            $table->string('name');                                     // "US Dollar"
            $table->string('symbol', 10);                               // "$", "Rp"
            $table->decimal('rate_to_pivot', 24, 12);                   // 1 unit = berapa USD
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->boolean('is_active')->default(false);
            $table->timestamp('rate_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
