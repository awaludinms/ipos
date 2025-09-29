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
        Schema::create('transaction_receipts', function (Blueprint $table) {
            $table->id();
            $table->integer('transaction_payment_id')->nullable();
            $table->integer('type')->nullable(); // (1) struk, (2) download pdf
            $table->integer('issued_by')->nullable(); // staff id
            $table->datetime('issued_at')->nullable(); // datetime tanggal di ambil
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_receipts');
    }
};
