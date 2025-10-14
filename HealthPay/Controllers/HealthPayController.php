<?php

namespace Plugins\PaymentChannels\HealthPay\Controllers;

use App\Http\Controllers\Controller;
use Plugins\PaymentChannels\HealthPay\Services\HealthPayService;
use App\Models\Order;
use App\Models\Accounting;
use App\Models\PaymentChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class HealthPayController extends Controller
{
    private $healthPayService;
    
    public function __construct()
    {
        $this->healthPayService = new HealthPayService();
    }
    
    /**
     * Display admin settings page
     */
    public function settings()
    {
        $settings = $this->getSettings();
        
        return view('healthpay::admin.settings', compact('settings'));
    }
    
    /**
     * Update admin settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'mode' => 'required|in:sandbox,live',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
            'api_endpoint' => 'required|in:sandbox,production',
            'webhook_secret' => 'nullable|string'
        ]);
        
        try {
            // Save settings to database
            $this->saveSettings($request->all());
            
            return redirect()->back()->with('success', 'HealthPay settings updated successfully');
            
        } catch (Exception $e) {
            Log::error('HealthPay settings update failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
    
    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $isValid = $this->healthPayService->validateCredentials();
            
            if ($isValid) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful! API credentials are valid.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection failed. Please check your API credentials.'
                ], 400);
            }
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Initiate payment
     */
    public function pay(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $order = Order::findOrFail($orderId);
            
            // Check if order is already paid
            if ($order->status === 'paid') {
                return redirect()->route('payment.success')
                    ->with('success', 'Order already paid');
            }
            
            // Create payment request
            $paymentRequest = $this->healthPayService->createPaymentRequest(
                $order->id,
                $order->amount,
                $order->user_id,
                "Payment for Order #{$order->id}"
            );
            
            // Log transaction
            $this->logTransaction($order, $paymentRequest, 'pending');
            
            // Update order with transaction reference
            $order->update([
                'reference_id' => $paymentRequest['createPaymentRequest']['id'] ?? null
            ]);
            
            // Redirect to payment URL
            $paymentUrl = $paymentRequest['createPaymentRequest']['paymentUrl'] ?? null;
            
            if ($paymentUrl) {
                return view('healthpay::payment.redirect', compact('paymentUrl', 'order'));
            } else {
                throw new Exception('Payment URL not received from HealthPay');
            }
            
        } catch (Exception $e) {
            Log::error('HealthPay payment initiation failed', [
                'order_id' => $orderId ?? null,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('payment.failed')
                ->with('error', $e->getMessage());
        }
    }
    
    /**
     * Handle return from HealthPay
     */
    public function return(Request $request)
    {
        try {
            $transactionId = $request->input('transaction_id');
            $referenceId = $request->input('reference_id');
            
            if (!$transactionId) {
                throw new Exception('Transaction ID not provided');
            }
            
            // Check transaction status
            $transaction = $this->healthPayService->checkTransactionStatus($transactionId);
            
            $order = Order::where('reference_id', $referenceId)
                ->orWhere('id', $referenceId)
                ->firstOrFail();
            
            if ($transaction['transaction']['status'] === 'SUCCESS') {
                return redirect()->route('payment.success')
                    ->with('success', 'Payment completed successfully');
            } else {
                return redirect()->route('payment.failed')
                    ->with('error', 'Payment was not completed');
            }
            
        } catch (Exception $e) {
            Log::error('HealthPay return handling failed', ['error' => $e->getMessage()]);
            
            return redirect()->route('payment.failed')
                ->with('error', 'Failed to process payment return');
        }
    }
    
    /**
     * Handle callback from HealthPay
     */
    public function callback(Request $request)
    {
        try {
            $transactionId = $request->input('transaction_id');
            $referenceId = $request->input('reference_id');
            
            if (!$transactionId) {
                throw new Exception('Transaction ID not provided');
            }
            
            // Verify transaction status
            $transaction = $this->healthPayService->checkTransactionStatus($transactionId);
            
            $order = Order::where('reference_id', $referenceId)
                ->orWhere('id', $referenceId)
                ->firstOrFail();
            
            if ($transaction['transaction']['status'] === 'SUCCESS') {
                // Update order status
                $order->update([
                    'status' => 'paid',
                    'payment_data' => json_encode($transaction)
                ]);
                
                // Create accounting record
                Accounting::create([
                    'user_id' => $order->user_id,
                    'amount' => $order->amount,
                    'type' => 'income',
                    'type_account' => 'asset',
                    'store_type' => Order::class,
                    'store_id' => $order->id,
                    'description' => "Payment for order #{$order->id} via HealthPay"
                ]);
                
                // Log successful transaction
                $this->logTransaction($order, $transaction, 'success');
                
                // Trigger post-payment actions
                if (method_exists($order, 'handleSuccessfulPayment')) {
                    $order->handleSuccessfulPayment();
                }
                
                return redirect()->route('payment.success')
                    ->with('success', trans('public.purchase_success'));
                    
            } else {
                $order->update(['status' => 'failed']);
                
                // Log failed transaction
                $this->logTransaction($order, $transaction, 'failed');
                
                return redirect()->route('payment.failed')
                    ->with('error', 'Payment failed or cancelled');
            }
            
        } catch (Exception $e) {
            Log::error('HealthPay callback handling failed', ['error' => $e->getMessage()]);
            
            return redirect()->route('payment.failed')
                ->with('error', $e->getMessage());
        }
    }
    
    /**
     * Handle webhook from HealthPay
     */
    public function webhook(Request $request)
    {
        try {
            // Verify webhook signature
            $signature = $request->header('X-HealthPay-Signature');
            $payload = $request->getContent();
            
            if (!$this->healthPayService->verifyWebhookSignature($payload, $signature)) {
                Log::warning('HealthPay webhook signature verification failed');
                return response()->json(['error' => 'Invalid signature'], 401);
            }
            
            $data = json_decode($payload, true);
            
            Log::info('HealthPay webhook received', ['event' => $data['event'] ?? 'unknown']);
            
            // Process webhook based on event type
            switch ($data['event'] ?? '') {
                case 'payment.success':
                    $this->handlePaymentSuccess($data);
                    break;
                    
                case 'payment.failed':
                    $this->handlePaymentFailed($data);
                    break;
                    
                case 'refund.completed':
                    $this->handleRefund($data);
                    break;
                    
                default:
                    Log::warning('Unknown webhook event', ['event' => $data['event'] ?? 'none']);
            }
            
            return response()->json(['status' => 'success']);
            
        } catch (Exception $e) {
            Log::error('HealthPay webhook processing failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle successful payment webhook
     */
    private function handlePaymentSuccess($data)
    {
        $order = Order::where('reference_id', $data['referenceId'] ?? '')
            ->orWhere('id', $data['referenceId'] ?? '')
            ->first();
        
        if ($order && $order->status !== 'paid') {
            $order->update([
                'status' => 'paid',
                'payment_data' => json_encode($data)
            ]);
            
            // Create accounting record
            Accounting::create([
                'user_id' => $order->user_id,
                'amount' => $order->amount,
                'type' => 'income',
                'type_account' => 'asset',
                'store_type' => Order::class,
                'store_id' => $order->id,
                'description' => "Payment for order #{$order->id} via HealthPay (Webhook)"
            ]);
            
            if (method_exists($order, 'handleSuccessfulPayment')) {
                $order->handleSuccessfulPayment();
            }
            
            Log::info('Payment success processed via webhook', ['order_id' => $order->id]);
        }
    }
    
    /**
     * Handle failed payment webhook
     */
    private function handlePaymentFailed($data)
    {
        $order = Order::where('reference_id', $data['referenceId'] ?? '')
            ->orWhere('id', $data['referenceId'] ?? '')
            ->first();
        
        if ($order) {
            $order->update(['status' => 'failed']);
            Log::info('Payment failure processed via webhook', ['order_id' => $order->id]);
        }
    }
    
    /**
     * Handle refund webhook
     */
    private function handleRefund($data)
    {
        $order = Order::where('reference_id', $data['referenceId'] ?? '')
            ->orWhere('id', $data['referenceId'] ?? '')
            ->first();
        
        if ($order) {
            $order->update(['status' => 'refunded']);
            
            // Create refund accounting record
            Accounting::create([
                'user_id' => $order->user_id,
                'amount' => -$order->amount,
                'type' => 'expense',
                'type_account' => 'asset',
                'store_type' => Order::class,
                'store_id' => $order->id,
                'description' => "Refund for order #{$order->id} via HealthPay"
            ]);
            
            Log::info('Refund processed via webhook', ['order_id' => $order->id]);
        }
    }
    
    /**
     * Log transaction
     */
    private function logTransaction($order, $transactionData, $status)
    {
        try {
            // This would save to healthpay_transactions table
            // Implementation depends on your database structure
            Log::info('HealthPay transaction logged', [
                'order_id' => $order->id,
                'status' => $status,
                'data' => $transactionData
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log transaction', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get settings from database
     */
    private function getSettings()
    {
        // This would fetch from database
        // For now, return config values
        return config('healthpay.settings');
    }
    
    /**
     * Save settings to database
     */
    private function saveSettings($settings)
    {
        // This would save to database
        // Implementation depends on your settings storage approach
        Log::info('HealthPay settings saved', ['settings' => $settings]);
    }
}

