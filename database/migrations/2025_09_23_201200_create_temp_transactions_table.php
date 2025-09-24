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
        Schema::create('temp_transactions', function (Blueprint $table) {
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
            $table->integer("transaction_state")->unsigned();
            $table->integer("customer_id")->unsigned()->nullable();
            $table->integer("reseller_id")->unsigned()->nullable();
            $table->text("keterangan")->nullable();
            $table->integer('transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_transactions');
    }
};
