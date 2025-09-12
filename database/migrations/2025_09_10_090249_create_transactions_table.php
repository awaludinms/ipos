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
            $table->string('transaction_number');
            $table->string('customer_name');
            $table->string('customer_type'); // reseller or customer
            $table->datetime(   'transaction_date');
            $table->double(   'grand_total');
            $table->double(   'paid'); // dibayar
            $table->boolean('pay_status'); // status pembayaran
            $table->double('change_return'); // kembalian
            $table->string('staff_name');
            $table->integer('staff_id');
            $table->string('transaction_pay_type');

            $table->integer('deleted_by')->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
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
