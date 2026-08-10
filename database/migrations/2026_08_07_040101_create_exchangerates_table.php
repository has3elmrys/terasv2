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
        Schema::create('exchangerates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            $table->date('date');                    // tanggal referensi kurs, BUKAN waktu fetch
            $table->decimal('rate_to_pivot', 24, 12);
            $table->string('source');                // provider mana yang melayani baris ini
            $table->timestamps();

            $table->unique(['currency_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchangerates');
    }
};
