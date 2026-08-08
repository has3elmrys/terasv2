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
        Schema::create('lendings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lender_id')->constrained()->restrictOnDelete();
            $table->foreignId('bank_id')->constrained()->restrictOnDelete();     // bank cash asal/tujuan
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();

            $table->enum('direction', ['lend', 'borrow']);   // Pinjamkan | Meminjam
            $table->decimal('amount', 20, 4);                 // principal, ditulis sekali saat create
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['active', 'partially_paid', 'paid', 'written_off'])->default('active');

            $table->text('note')->nullable();
            $table->string('image_path')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'status']);
            $table->index(['lender_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lendings');
    }
};
