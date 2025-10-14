<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHealthpayTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('healthpay_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->comment('Reference to orders table');
            $table->unsignedBigInteger('user_id')->comment('Reference to users table');
            $table->string('transaction_id')->unique()->comment('HealthPay transaction ID');
            $table->string('reference_id')->nullable()->comment('Order reference ID');
            $table->decimal('amount', 10, 2)->comment('Transaction amount');
            $table->string('currency', 3)->default('EGP')->comment('Currency code');
            $table->enum('status', ['pending', 'success', 'failed', 'refunded', 'cancelled'])->default('pending')->comment('Transaction status');
            $table->enum('type', ['payment', 'refund', 'wallet_deduct', 'wallet_add'])->default('payment')->comment('Transaction type');
            $table->text('description')->nullable()->comment('Transaction description');
            $table->text('response_data')->nullable()->comment('Full API response in JSON');
            $table->string('payment_url')->nullable()->comment('HealthPay payment URL');
            $table->string('webhook_signature')->nullable()->comment('Webhook signature for verification');
            $table->timestamp('completed_at')->nullable()->comment('When transaction was completed');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('transaction_id');
            $table->index('reference_id');
            $table->index('status');
            $table->index('type');
            $table->index(['user_id', 'status']);
            $table->index(['order_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('healthpay_transactions');
    }
}

