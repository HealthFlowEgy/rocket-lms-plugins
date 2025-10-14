<?php

namespace Plugins\PaymentChannels\HealthPay\Services;

use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Plugins\PaymentChannels\HealthPay\Models\HealthPaySetting;

class HealthPayService
{
    private $apiUrl;
    private $apiKey;
    private $apiSecret;
    private $webhookSecret;
    private $client;
    private $mode;
    
    // Constants
    const CURRENCY_EGP = 'EGP';
    const TOKEN_CACHE_DURATION = 3600; // 1 hour
    const API_TIMEOUT = 30;
    
    public function __construct()
    {
        $settings = $this->getSettings();
        
        $this->mode = $settings['mode'] ?? 'sandbox';
        $this->apiUrl = $this->getApiEndpoint($settings['api_endpoint'] ?? 'sandbox');
        $this->apiKey = $settings['api_key'] ?? '';
        $this->apiSecret = $settings['api_secret'] ?? '';
        $this->webhookSecret = $settings['webhook_secret'] ?? '';
        
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'timeout' => self::API_TIMEOUT,
            'verify' => true,
        ]);
    }
    
    /**
     * Get API endpoint based on environment
     */
    private function getApiEndpoint(string $environment): string
    {
        $endpoints = config('healthpay.settings.api_endpoint.options', [
            'sandbox' => 'https://api.beta.healthpay.tech/graphql',
            'production' => 'https://api.healthpay.tech/graphql'
        ]);
        
        return $endpoints[$environment] ?? $endpoints['sandbox'];
    }
    
    /**
     * Get plugin settings from database
     */
    private function getSettings(): array
    {
        try {
            $setting = HealthPaySetting::first();
            
            if ($setting && $setting->settings) {
                return is_array($setting->settings) ? $setting->settings : json_decode($setting->settings, true);
            }
        } catch (Exception $e) {
            Log::warning('HealthPay: Could not fetch settings from database', ['error' => $e->getMessage()]);
        }
        
        // Fallback to config
        return [
            'mode' => config('healthpay.settings.mode.default', 'sandbox'),
            'api_endpoint' => config('healthpay.settings.api_endpoint.default', 'sandbox'),
            'api_key' => env('HEALTHPAY_API_KEY', ''),
            'api_secret' => env('HEALTHPAY_API_SECRET', ''),
            'webhook_secret' => env('HEALTHPAY_WEBHOOK_SECRET', ''),
            'enabled' => config('healthpay.settings.enabled.default', false),
        ];
    }
    
    /**
     * Execute GraphQL query
     */
    private function executeQuery(string $query, array $variables = []): array
    {
        try {
            $response = $this->client->post('', [
                'json' => [
                    'query' => $query,
                    'variables' => $variables
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->getAuthToken(),
                    'X-API-Key' => $this->apiKey,
                ]
            ]);
            
            $body = json_decode($response->getBody(), true);
            
            if (isset($body['errors'])) {
                Log::error('HealthPay API Error', [
                    'errors' => $body['errors'],
                    'query' => substr($query, 0, 200),
                    'variables' => $variables
                ]);
                throw new Exception($body['errors'][0]['message'] ?? 'Unknown API error');
            }
            
            return $body['data'] ?? [];
            
        } catch (Exception $e) {
            Log::error('HealthPay API Exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw new Exception('HealthPay API Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Authenticate and get token
     */
    private function getAuthToken(): string
    {
        $cacheKey = 'healthpay_auth_token_' . $this->mode;
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // TODO: Implement proper OAuth based on HealthPay API documentation
        $token = $this->apiKey;
        
        Cache::put($cacheKey, $token, self::TOKEN_CACHE_DURATION);
        
        return $token;
    }
    
    /**
     * Create payment request
     */
    public function createPaymentRequest(int $orderId, float $amount, int $userId, string $description): array
    {
        $query = <<<'GQL'
        mutation CreatePaymentRequest($input: PaymentRequestInput!) {
            createPaymentRequest(input: $input) {
                id
                status
                amount
                referenceId
                paymentUrl
                createdAt
            }
        }
        GQL;
        
        $variables = [
            'input' => [
                'amount' => $amount,
                'currency' => self::CURRENCY_EGP,
                'referenceId' => (string) $orderId,
                'userId' => (string) $userId,
                'description' => $description,
                'callbackUrl' => route('healthpay.callback'),
                'returnUrl' => route('healthpay.return'),
                'webhookUrl' => route('healthpay.webhook')
            ]
        ];
        
        return $this->executeQuery($query, $variables);
    }
    
    /**
     * Check transaction status
     */
    public function checkTransactionStatus(string $transactionId): array
    {
        $query = <<<'GQL'
        query GetTransaction($id: ID!) {
            transaction(id: $id) {
                id
                status
                amount
                currency
                referenceId
                userId
                createdAt
                updatedAt
            }
        }
        GQL;
        
        return $this->executeQuery($query, ['id' => $transactionId]);
    }
    
    /**
     * Get user balance
     */
    public function getUserBalance(int $userId): array
    {
        $query = <<<'GQL'
        query GetUserBalance($userId: ID!) {
            user(id: $userId) {
                id
                wallet {
                    balance
                    currency
                    availableBalance
                }
            }
        }
        GQL;
        
        return $this->executeQuery($query, ['userId' => $userId]);
    }
    
    /**
     * Deduct from wallet
     */
    public function deductFromWallet(int $userId, float $amount, int $orderId, string $description = ''): array
    {
        $query = <<<'GQL'
        mutation DeductFromWallet($input: WalletDeductInput!) {
            deductFromWallet(input: $input) {
                success
                transactionId
                remainingBalance
                message
            }
        }
        GQL;
        
        $variables = [
            'input' => [
                'userId' => (string) $userId,
                'amount' => $amount,
                'referenceId' => (string) $orderId,
                'description' => $description
            ]
        ];
        
        return $this->executeQuery($query, $variables);
    }
    
    /**
     * Add to wallet
     */
    public function addToWallet(int $userId, float $amount, int $orderId, string $description = ''): array
    {
        $query = <<<'GQL'
        mutation AddToWallet($input: WalletAddInput!) {
            addToWallet(input: $input) {
                success
                transactionId
                newBalance
                message
            }
        }
        GQL;
        
        $variables = [
            'input' => [
                'userId' => (string) $userId,
                'amount' => $amount,
                'referenceId' => (string) $orderId,
                'description' => $description
            ]
        ];
        
        return $this->executeQuery($query, $variables);
    }
    
    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            Log::warning('HealthPay webhook secret not configured');
            
            if (config('app.env') === 'local' || config('app.debug') === true) {
                return true;
            }
            
            return false;
        }
        
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Get transaction history
     */
    public function getTransactionHistory(int $userId, int $limit = 10, int $offset = 0): array
    {
        $query = <<<'GQL'
        query GetTransactionHistory($userId: ID!, $limit: Int, $offset: Int) {
            user(id: $userId) {
                transactions(limit: $limit, offset: $offset) {
                    id
                    amount
                    currency
                    status
                    type
                    description
                    createdAt
                }
            }
        }
        GQL;
        
        $variables = [
            'userId' => $userId,
            'limit' => $limit,
            'offset' => $offset
        ];
        
        return $this->executeQuery($query, $variables);
    }
    
    /**
     * Refund transaction
     */
    public function refundTransaction(string $transactionId, ?float $amount = null, string $reason = ''): array
    {
        $query = <<<'GQL'
        mutation RefundTransaction($input: RefundInput!) {
            refundTransaction(input: $input) {
                success
                refundId
                amount
                message
            }
        }
        GQL;
        
        $variables = [
            'input' => [
                'transactionId' => $transactionId,
                'amount' => $amount,
                'reason' => $reason
            ]
        ];
        
        return $this->executeQuery($query, $variables);
    }
    
    /**
     * Validate API credentials
     */
    public function validateCredentials(): bool
    {
        try {
            $query = <<<'GQL'
            query ValidateCredentials {
                me {
                    id
                    email
                    status
                }
            }
            GQL;
            
            $result = $this->executeQuery($query);
            
            return !empty($result);
            
        } catch (Exception $e) {
            Log::error('HealthPay credential validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

