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
        Schema::create('temp_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->integer('temp_transaction_id')->nullable();
            $table->integer('product_id')->nullable()->unsigned();
            $table->integer('product_qty')->unsigned();
            $table->double('product_price')->unsigned();
            $table->double('product_subtotal')->unsigned();
            $table->integer('deleted_by')->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->string("product_name")->nullable();
            $table->text("notes")->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_transaction_details');
    }
};
