<?php

namespace Plugins\PaymentChannels\HealthPay\Services;

use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HealthPayService
{
    private $apiUrl;
    private $apiKey;
    private $apiSecret;
    private $webhookSecret;
    private $client;
    private $mode;
    
    public function __construct()
    {
        $settings = $this->getSettings();
        
        $this->mode = $settings['mode'] ?? 'sandbox';
        $this->apiUrl = $settings['api_endpoint'] ?? 'https://api.beta.healthpay.tech/graphql';
        $this->apiKey = $settings['api_key'] ?? '';
        $this->apiSecret = $settings['api_secret'] ?? '';
        $this->webhookSecret = $settings['webhook_secret'] ?? '';
        
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'timeout' => 30.0,
            'verify' => true,
        ]);
    }
    
    /**
     * Get plugin settings from database
     */
    private function getSettings()
    {
        // This would typically fetch from database
        // For now, return config values
        return config('healthpay');
    }
    
    /**
     * Execute GraphQL query
     */
    private function executeQuery($query, $variables = [])
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
                    'query' => $query,
                    'variables' => $variables
                ]);
                throw new Exception($body['errors'][0]['message'] ?? 'Unknown API error');
            }
            
            return $body['data'] ?? [];
            
        } catch (Exception $e) {
            Log::error('HealthPay API Exception', [
                'message' => $e->getMessage(),
                'query' => $query
            ]);
            throw new Exception('HealthPay API Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Authenticate and get token
     */
    private function getAuthToken()
    {
        $cacheKey = 'healthpay_auth_token_' . $this->mode;
        
        // Check if token is cached
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // For now, use API key as token
        // In production, implement proper OAuth or token retrieval
        $token = $this->apiKey;
        
        // Cache token for 1 hour
        Cache::put($cacheKey, $token, 3600);
        
        return $token;
    }
    
    /**
     * Create payment request
     */
    public function createPaymentRequest($orderId, $amount, $userId, $description)
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
                'amount' => (float) $amount,
                'currency' => 'EGP',
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
    public function checkTransactionStatus($transactionId)
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
        
        $variables = ['id' => $transactionId];
        
        return $this->executeQuery($query, $variables);
    }
    
    /**
     * Get user balance
     */
    public function getUserBalance($userId)
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
        
        $variables = ['userId' => $userId];
        
        return $this->executeQuery($query, $variables);
    }
    
    /**
     * Deduct from wallet
     */
    public function deductFromWallet($userId, $amount, $orderId, $description = '')
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
                'amount' => (float) $amount,
                'referenceId' => (string) $orderId,
                'description' => $description
            ]
        ];
        
        return $this->executeQuery($query, $variables);
    }
    
    /**
     * Add to wallet
     */
    public function addToWallet($userId, $amount, $orderId, $description = '')
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
                'amount' => (float) $amount,
                'referenceId' => (string) $orderId,
                'description' => $description
            ]
        ];
        
        return $this->executeQuery($query, $variables);
    }
    
    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        if (empty($this->webhookSecret)) {
            Log::warning('HealthPay webhook secret not configured');
            return true; // Allow in development
        }
        
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Get transaction history
     */
    public function getTransactionHistory($userId, $limit = 10, $offset = 0)
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
    public function refundTransaction($transactionId, $amount = null, $reason = '')
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
    public function validateCredentials()
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
            return false;
        }
    }
}

