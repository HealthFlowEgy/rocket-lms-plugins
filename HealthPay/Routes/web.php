<?php

use Illuminate\Support\Facades\Route;
use Plugins\PaymentChannels\HealthPay\Controllers\HealthPayController;

/*
|--------------------------------------------------------------------------
| HealthPay Payment Gateway Routes
|--------------------------------------------------------------------------
|
| These routes handle all HealthPay payment gateway operations including
| admin settings, payment processing, callbacks, and webhooks.
|
*/

// Admin Routes
Route::group([
    'prefix' => 'admin/healthpay',
    'as' => 'admin.healthpay.',
    'middleware' => ['auth', 'admin']
], function () {
    
    // Settings
    Route::get('/settings', [HealthPayController::class, 'settings'])
        ->name('settings');
    
    Route::post('/settings', [HealthPayController::class, 'updateSettings'])
        ->name('update');
    
    // Test Connection
    Route::post('/test-connection', [HealthPayController::class, 'testConnection'])
        ->name('test');
    
});

// Payment Routes
Route::group([
    'prefix' => 'payments/healthpay',
    'as' => 'healthpay.',
    'middleware' => ['web']
], function () {
    
    // Initiate Payment
    Route::post('/pay', [HealthPayController::class, 'pay'])
        ->name('pay')
        ->middleware('auth');
    
    // Return URL (User redirected back after payment)
    Route::get('/return', [HealthPayController::class, 'return'])
        ->name('return');
    
    // Callback URL (Backend notification)
    Route::post('/callback', [HealthPayController::class, 'callback'])
        ->name('callback');
    
    // Webhook URL (Server-to-server notifications)
    Route::post('/webhook', [HealthPayController::class, 'webhook'])
        ->name('webhook');
    
});

// Public Routes (No authentication required)
Route::group([
    'prefix' => 'healthpay',
    'as' => 'healthpay.public.',
], function () {
    
    // Payment Status Check
    Route::get('/status/{orderId}', [HealthPayController::class, 'checkStatus'])
        ->name('status');
    
});

