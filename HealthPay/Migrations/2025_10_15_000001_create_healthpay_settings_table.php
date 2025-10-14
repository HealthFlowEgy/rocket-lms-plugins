<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHealthpaySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('healthpay_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false)->comment('Enable/Disable HealthPay gateway');
            $table->enum('mode', ['sandbox', 'live'])->default('sandbox')->comment('Operating mode');
            $table->string('api_key')->nullable()->comment('HealthPay API Key');
            $table->string('api_secret')->nullable()->comment('HealthPay API Secret');
            $table->enum('api_endpoint', ['sandbox', 'production'])->default('sandbox')->comment('API endpoint selection');
            $table->string('webhook_secret')->nullable()->comment('Webhook signature verification secret');
            $table->json('settings')->nullable()->comment('Additional settings in JSON format');
            $table->timestamp('last_tested_at')->nullable()->comment('Last time credentials were tested');
            $table->boolean('credentials_valid')->default(false)->comment('Whether credentials are valid');
            $table->timestamps();
            
            // Indexes
            $table->index('enabled');
            $table->index('mode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('healthpay_settings');
    }
}

