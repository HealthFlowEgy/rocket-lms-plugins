<?php

namespace Plugins\PaymentChannels\HealthPay;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class HealthPayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/config.php', 'healthpay'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        
        // Load views
        $this->loadViewsFrom(__DIR__ . '/Views', 'healthpay');
        
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        
        // Publish assets
        $this->publishes([
            __DIR__ . '/assets' => public_path('plugins/HealthPay/assets'),
        ], 'healthpay-assets');
        
        // Publish config
        $this->publishes([
            __DIR__ . '/config.php' => config_path('healthpay.php'),
        ], 'healthpay-config');
        
        // Publish views
        $this->publishes([
            __DIR__ . '/Views' => resource_path('views/vendor/healthpay'),
        ], 'healthpay-views');
        
        // Publish migrations
        $this->publishes([
            __DIR__ . '/Migrations' => database_path('migrations'),
        ], 'healthpay-migrations');
        
        // Register payment channel
        $this->registerPaymentChannel();
    }
    
    /**
     * Register HealthPay as a payment channel
     *
     * @return void
     */
    protected function registerPaymentChannel()
    {
        // This would integrate with Rocket LMS payment channel system
        // The exact implementation depends on Rocket LMS architecture
        
        if (class_exists('\App\Models\PaymentChannel')) {
            // Register HealthPay in the payment channels list
            // This is a placeholder - actual implementation may vary
        }
    }
}

