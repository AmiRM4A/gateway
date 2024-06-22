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
            $table->string('order_id', 50)->unique()->comment('شماره سفارش پذیرنده');
            $table->string('transaction_id',128)->unique()->comment('کلید منحصر بفرد تراکنش');
            $table->unsignedBigInteger('amount')->comment('مبلغ مورد نظر ترانکش برای پرداخت');
            $table->string('link', 128)->unique()->comment('لینک پرداخت برای انتقال خریدار به درگاه پرداخت');
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
