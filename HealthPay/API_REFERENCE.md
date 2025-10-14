# HealthPay Plugin API Reference

Complete API reference for the HealthPay payment gateway plugin for Rocket LMS.

## Table of Contents

1. [GraphQL API Operations](#graphql-api-operations)
2. [Service Methods](#service-methods)
3. [Controller Methods](#controller-methods)
4. [Model Methods](#model-methods)
5. [Routes](#routes)
6. [Webhooks](#webhooks)

---

## GraphQL API Operations

The plugin communicates with HealthPay using GraphQL API.

### Base URLs

- **Sandbox**: `https://api.beta.healthpay.tech/graphql`
- **Production**: `https://api.healthpay.tech/graphql`

### Authentication

All requests require authentication headers:

```
Authorization: Bearer {API_KEY}
X-API-Key: {API_KEY}
Content-Type: application/json
```

### 1. Create Payment Request

Creates a new payment request for an order.

**Operation**: `mutation`

**Query**:
```graphql
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
```

**Variables**:
```json
{
  "input": {
    "amount": 100.00,
    "currency": "EGP",
    "referenceId": "ORDER_123",
    "userId": "USER_456",
    "description": "Payment for Order #123",
    "callbackUrl": "https://yourdomain.com/payments/healthpay/callback",
    "returnUrl": "https://yourdomain.com/payments/healthpay/return",
    "webhookUrl": "https://yourdomain.com/payments/healthpay/webhook"
  }
}
```

**Response**:
```json
{
  "data": {
    "createPaymentRequest": {
      "id": "PAY_789",
      "status": "PENDING",
      "amount": 100.00,
      "referenceId": "ORDER_123",
      "paymentUrl": "https://pay.healthpay.tech/checkout/PAY_789",
      "createdAt": "2025-10-15T10:30:00Z"
    }
  }
}
```

### 2. Check Transaction Status

Retrieves the current status of a transaction.

**Operation**: `query`

**Query**:
```graphql
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
```

**Variables**:
```json
{
  "id": "PAY_789"
}
```

**Response**:
```json
{
  "data": {
    "transaction": {
      "id": "PAY_789",
      "status": "SUCCESS",
      "amount": 100.00,
      "currency": "EGP",
      "referenceId": "ORDER_123",
      "userId": "USER_456",
      "createdAt": "2025-10-15T10:30:00Z",
      "updatedAt": "2025-10-15T10:35:00Z"
    }
  }
}
```

### 3. Get User Balance

Retrieves the wallet balance for a user.

**Operation**: `query`

**Query**:
```graphql
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
```

**Variables**:
```json
{
  "userId": "USER_456"
}
```

**Response**:
```json
{
  "data": {
    "user": {
      "id": "USER_456",
      "wallet": {
        "balance": 500.00,
        "currency": "EGP",
        "availableBalance": 450.00
      }
    }
  }
}
```

### 4. Deduct from Wallet

Deducts an amount from a user's wallet.

**Operation**: `mutation`

**Query**:
```graphql
mutation DeductFromWallet($input: WalletDeductInput!) {
    deductFromWallet(input: $input) {
        success
        transactionId
        remainingBalance
        message
    }
}
```

**Variables**:
```json
{
  "input": {
    "userId": "USER_456",
    "amount": 100.00,
    "referenceId": "ORDER_123",
    "description": "Payment for course"
  }
}
```

**Response**:
```json
{
  "data": {
    "deductFromWallet": {
      "success": true,
      "transactionId": "TXN_999",
      "remainingBalance": 400.00,
      "message": "Amount deducted successfully"
    }
  }
}
```

### 5. Add to Wallet

Adds an amount to a user's wallet.

**Operation**: `mutation`

**Query**:
```graphql
mutation AddToWallet($input: WalletAddInput!) {
    addToWallet(input: $input) {
        success
        transactionId
        newBalance
        message
    }
}
```

**Variables**:
```json
{
  "input": {
    "userId": "USER_456",
    "amount": 200.00,
    "referenceId": "REFUND_123",
    "description": "Refund for cancelled order"
  }
}
```

**Response**:
```json
{
  "data": {
    "addToWallet": {
      "success": true,
      "transactionId": "TXN_1000",
      "newBalance": 600.00,
      "message": "Amount added successfully"
    }
  }
}
```

### 6. Refund Transaction

Processes a refund for a completed transaction.

**Operation**: `mutation`

**Query**:
```graphql
mutation RefundTransaction($input: RefundInput!) {
    refundTransaction(input: $input) {
        success
        refundId
        amount
        message
    }
}
```

**Variables**:
```json
{
  "input": {
    "transactionId": "PAY_789",
    "amount": 100.00,
    "reason": "Customer requested refund"
  }
}
```

**Response**:
```json
{
  "data": {
    "refundTransaction": {
      "success": true,
      "refundId": "REF_111",
      "amount": 100.00,
      "message": "Refund processed successfully"
    }
  }
}
```

---

## Service Methods

### HealthPayService Class

Located in: `Services/HealthPayService.php`

#### `createPaymentRequest($orderId, $amount, $userId, $description)`

Creates a payment request.

**Parameters**:
- `$orderId` (string): Order ID
- `$amount` (float): Payment amount
- `$userId` (string): User ID
- `$description` (string): Payment description

**Returns**: `array` - Payment request data

**Example**:
```php
$service = new HealthPayService();
$result = $service->createPaymentRequest(
    'ORDER_123',
    100.00,
    'USER_456',
    'Payment for Order #123'
);
```

#### `checkTransactionStatus($transactionId)`

Checks the status of a transaction.

**Parameters**:
- `$transactionId` (string): Transaction ID

**Returns**: `array` - Transaction data

**Example**:
```php
$status = $service->checkTransactionStatus('PAY_789');
```

#### `getUserBalance($userId)`

Gets user's wallet balance.

**Parameters**:
- `$userId` (string): User ID

**Returns**: `array` - Wallet data

**Example**:
```php
$balance = $service->getUserBalance('USER_456');
```

#### `deductFromWallet($userId, $amount, $orderId, $description = '')`

Deducts amount from user's wallet.

**Parameters**:
- `$userId` (string): User ID
- `$amount` (float): Amount to deduct
- `$orderId` (string): Order ID
- `$description` (string): Optional description

**Returns**: `array` - Deduction result

**Example**:
```php
$result = $service->deductFromWallet('USER_456', 100.00, 'ORDER_123', 'Course payment');
```

#### `addToWallet($userId, $amount, $orderId, $description = '')`

Adds amount to user's wallet.

**Parameters**:
- `$userId` (string): User ID
- `$amount` (float): Amount to add
- `$orderId` (string): Order ID
- `$description` (string): Optional description

**Returns**: `array` - Addition result

**Example**:
```php
$result = $service->addToWallet('USER_456', 200.00, 'REFUND_123', 'Order refund');
```

#### `verifyWebhookSignature($payload, $signature)`

Verifies webhook signature.

**Parameters**:
- `$payload` (string): Raw webhook payload
- `$signature` (string): Signature from header

**Returns**: `bool` - Verification result

**Example**:
```php
$isValid = $service->verifyWebhookSignature($payload, $signature);
```

#### `validateCredentials()`

Validates API credentials.

**Returns**: `bool` - Validation result

**Example**:
```php
$isValid = $service->validateCredentials();
```

---

## Controller Methods

### HealthPayController Class

Located in: `Controllers/HealthPayController.php`

#### `settings()`

Displays admin settings page.

**Route**: `GET /admin/healthpay/settings`

**Returns**: `View`

#### `updateSettings(Request $request)`

Updates plugin settings.

**Route**: `POST /admin/healthpay/settings`

**Parameters**: Form data from settings page

**Returns**: `Redirect`

#### `testConnection()`

Tests API connection.

**Route**: `POST /admin/healthpay/test-connection`

**Returns**: `JsonResponse`

#### `pay(Request $request)`

Initiates payment.

**Route**: `POST /payments/healthpay/pay`

**Parameters**:
- `order_id` (required): Order ID

**Returns**: `View` - Redirect page

#### `return(Request $request)`

Handles return from payment.

**Route**: `GET /payments/healthpay/return`

**Parameters**:
- `transaction_id` (required): Transaction ID
- `reference_id` (required): Reference ID

**Returns**: `Redirect`

#### `callback(Request $request)`

Handles payment callback.

**Route**: `POST /payments/healthpay/callback`

**Parameters**:
- `transaction_id` (required): Transaction ID
- `reference_id` (required): Reference ID

**Returns**: `Redirect`

#### `webhook(Request $request)`

Handles webhook notifications.

**Route**: `POST /payments/healthpay/webhook`

**Headers**:
- `X-HealthPay-Signature`: Webhook signature

**Returns**: `JsonResponse`

---

## Model Methods

### HealthPayTransaction Model

Located in: `Models/HealthPayTransaction.php`

#### Relationships

```php
// Get associated order
$transaction->order();

// Get associated user
$transaction->user();
```

#### Status Methods

```php
// Check if successful
$transaction->isSuccessful();

// Check if pending
$transaction->isPending();

// Check if failed
$transaction->isFailed();

// Check if refunded
$transaction->isRefunded();
```

#### Update Methods

```php
// Mark as successful
$transaction->markAsSuccessful($responseData);

// Mark as failed
$transaction->markAsFailed($responseData);

// Mark as refunded
$transaction->markAsRefunded($responseData);
```

#### Query Scopes

```php
// Get successful transactions
HealthPayTransaction::successful()->get();

// Get pending transactions
HealthPayTransaction::pending()->get();

// Get failed transactions
HealthPayTransaction::failed()->get();

// Get user transactions
HealthPayTransaction::forUser($userId)->get();

// Get order transactions
HealthPayTransaction::forOrder($orderId)->get();
```

### HealthPaySetting Model

Located in: `Models/HealthPaySetting.php`

#### Singleton Instance

```php
$settings = HealthPaySetting::getInstance();
```

#### Status Methods

```php
// Check if enabled
$settings->isEnabled();

// Check if sandbox
$settings->isSandbox();

// Check if live
$settings->isLive();
```

#### Configuration Methods

```php
// Get API URL
$apiUrl = $settings->getApiUrl();

// Get portal URL
$portalUrl = $settings->getPortalUrl();

// Check if credentials configured
$hasCredentials = $settings->hasCredentials();
```

#### Settings Management

```php
// Get setting value
$value = $settings->getSetting('key', 'default');

// Set setting value
$settings->setSetting('key', 'value');

// Mark as tested
$settings->markAsTested($isValid);
```

---

## Routes

### Admin Routes

| Method | URI | Name | Middleware |
|--------|-----|------|------------|
| GET | `/admin/healthpay/settings` | `admin.healthpay.settings` | auth, admin |
| POST | `/admin/healthpay/settings` | `admin.healthpay.update` | auth, admin |
| POST | `/admin/healthpay/test-connection` | `admin.healthpay.test` | auth, admin |

### Payment Routes

| Method | URI | Name | Middleware |
|--------|-----|------|------------|
| POST | `/payments/healthpay/pay` | `healthpay.pay` | web, auth |
| GET | `/payments/healthpay/return` | `healthpay.return` | web |
| POST | `/payments/healthpay/callback` | `healthpay.callback` | web |
| POST | `/payments/healthpay/webhook` | `healthpay.webhook` | web |

---

## Webhooks

### Webhook Events

#### payment.success

Triggered when payment is completed successfully.

**Payload**:
```json
{
  "event": "payment.success",
  "transactionId": "PAY_789",
  "referenceId": "ORDER_123",
  "amount": 100.00,
  "currency": "EGP",
  "userId": "USER_456",
  "timestamp": "2025-10-15T10:35:00Z"
}
```

#### payment.failed

Triggered when payment fails or is cancelled.

**Payload**:
```json
{
  "event": "payment.failed",
  "transactionId": "PAY_789",
  "referenceId": "ORDER_123",
  "reason": "Insufficient funds",
  "timestamp": "2025-10-15T10:35:00Z"
}
```

#### refund.completed

Triggered when refund is processed.

**Payload**:
```json
{
  "event": "refund.completed",
  "refundId": "REF_111",
  "transactionId": "PAY_789",
  "referenceId": "ORDER_123",
  "amount": 100.00,
  "currency": "EGP",
  "timestamp": "2025-10-15T11:00:00Z"
}
```

### Webhook Signature Verification

```php
$signature = $request->header('X-HealthPay-Signature');
$payload = $request->getContent();
$webhookSecret = config('healthpay.webhook_secret');

$expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

if (hash_equals($expectedSignature, $signature)) {
    // Signature valid
} else {
    // Signature invalid
}
```

---

## Error Handling

### Common Error Responses

#### Invalid Credentials

```json
{
  "errors": [
    {
      "message": "Invalid API credentials",
      "code": "INVALID_CREDENTIALS"
    }
  ]
}
```

#### Insufficient Funds

```json
{
  "errors": [
    {
      "message": "Insufficient wallet balance",
      "code": "INSUFFICIENT_FUNDS"
    }
  ]
}
```

#### Transaction Not Found

```json
{
  "errors": [
    {
      "message": "Transaction not found",
      "code": "TRANSACTION_NOT_FOUND"
    }
  ]
}
```

---

## Rate Limiting

HealthPay API implements rate limiting:

- **Sandbox**: 100 requests per minute
- **Production**: 1000 requests per minute

Exceeded limits return:

```json
{
  "errors": [
    {
      "message": "Rate limit exceeded",
      "code": "RATE_LIMIT_EXCEEDED",
      "retryAfter": 60
    }
  ]
}
```

---

**API Reference Version**: 1.0.0  
**Last Updated**: October 15, 2025

