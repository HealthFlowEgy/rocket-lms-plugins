<?php

namespace Plugins\PaymentChannels\HealthPay\Models;

use Illuminate\Database\Eloquent\Model;

class HealthPaySetting extends Model
{
    protected $table = 'healthpay_settings';
    
    protected $fillable = [
        'enabled',
        'mode',
        'api_key',
        'api_secret',
        'api_endpoint',
        'webhook_secret',
        'settings',
        'last_tested_at',
        'credentials_valid'
    ];
    
    protected $casts = [
        'enabled' => 'boolean',
        'credentials_valid' => 'boolean',
        'settings' => 'array',
        'last_tested_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Get the singleton instance
     */
    public static function getInstance()
    {
        $setting = self::first();
        
        if (!$setting) {
            $setting = self::create([
                'enabled' => false,
                'mode' => 'sandbox',
                'api_endpoint' => 'sandbox',
                'credentials_valid' => false
            ]);
        }
        
        return $setting;
    }
    
    /**
     * Check if gateway is enabled
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
    
    /**
     * Check if in sandbox mode
     */
    public function isSandbox()
    {
        return $this->mode === 'sandbox';
    }
    
    /**
     * Check if in live mode
     */
    public function isLive()
    {
        return $this->mode === 'live';
    }
    
    /**
     * Get API URL based on endpoint setting
     */
    public function getApiUrl()
    {
        return $this->api_endpoint === 'production'
            ? 'https://api.healthpay.tech/graphql'
            : 'https://api.beta.healthpay.tech/graphql';
    }
    
    /**
     * Get portal URL based on endpoint setting
     */
    public function getPortalUrl()
    {
        return $this->api_endpoint === 'production'
            ? 'https://portal.healthpay.tech'
            : 'https://portal.beta.healthpay.tech';
    }
    
    /**
     * Check if credentials are configured
     */
    public function hasCredentials()
    {
        return !empty($this->api_key) && !empty($this->api_secret);
    }
    
    /**
     * Mark credentials as tested
     */
    public function markAsTested($isValid = true)
    {
        $this->update([
            'last_tested_at' => now(),
            'credentials_valid' => $isValid
        ]);
    }
    
    /**
     * Get setting value by key
     */
    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
    
    /**
     * Set setting value by key
     */
    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->update(['settings' => $settings]);
    }
}

