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
        //
        Schema::table("transactions", function (Blueprint $table) {
            $table->integer("transaction_state")->unsigned()->nullable()->after("transaction_pay_type")->default(1); // (1) new, (2) done-payed, (3) credit, (4) DP
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
