<?php

namespace Plugins\PaymentChannels\HealthPay\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\User;

class HealthPayTransaction extends Model
{
    protected $table = 'healthpay_transactions';
    
    protected $fillable = [
        'order_id',
        'user_id',
        'transaction_id',
        'reference_id',
        'amount',
        'currency',
        'status',
        'type',
        'description',
        'response_data',
        'payment_url',
        'webhook_signature',
        'completed_at'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Get the order associated with this transaction
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Get the user associated with this transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Check if transaction is successful
     */
    public function isSuccessful()
    {
        return $this->status === 'success';
    }
    
    /**
     * Check if transaction is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }
    
    /**
     * Check if transaction has failed
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }
    
    /**
     * Check if transaction is refunded
     */
    public function isRefunded()
    {
        return $this->status === 'refunded';
    }
    
    /**
     * Mark transaction as successful
     */
    public function markAsSuccessful($responseData = null)
    {
        $this->update([
            'status' => 'success',
            'completed_at' => now(),
            'response_data' => $responseData ? json_encode($responseData) : $this->response_data
        ]);
    }
    
    /**
     * Mark transaction as failed
     */
    public function markAsFailed($responseData = null)
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'response_data' => $responseData ? json_encode($responseData) : $this->response_data
        ]);
    }
    
    /**
     * Mark transaction as refunded
     */
    public function markAsRefunded($responseData = null)
    {
        $this->update([
            'status' => 'refunded',
            'completed_at' => now(),
            'response_data' => $responseData ? json_encode($responseData) : $this->response_data
        ]);
    }
    
    /**
     * Scope for successful transactions
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }
    
    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope for failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
    
    /**
     * Scope for user transactions
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    /**
     * Scope for order transactions
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }
}

