# HealthPay Payment Gateway Plugin for Rocket LMS

A comprehensive payment gateway integration plugin that enables Rocket LMS to accept payments through HealthPay, a local Egyptian payment solution.

## Overview

HealthPay is a modern payment gateway designed for the Egyptian market, offering wallet-based transactions, direct account management, and GraphQL API integration. This plugin seamlessly integrates HealthPay into Rocket LMS, providing a secure and efficient payment processing solution.

## Features

- **GraphQL API Integration**: Modern API architecture for efficient communication
- **Wallet-Based Transactions**: Support for HealthPay wallet payments
- **Direct Account Operations**: Add/deduct funds directly from user accounts
- **Payment Request Creation**: Generate secure payment requests
- **Transaction Tracking**: Comprehensive transaction logging and monitoring
- **Webhook Support**: Real-time payment status updates
- **Sandbox & Live Modes**: Test in sandbox before going live
- **Admin Dashboard**: Easy-to-use settings and statistics interface
- **Multi-Currency Support**: Currently supports EGP (Egyptian Pound)
- **Secure Authentication**: API key and secret-based authentication
- **Transaction History**: Complete audit trail of all transactions

## Requirements

- **Rocket LMS**: Version 2.0.0 or higher
- **PHP**: Version 8.0 or higher
- **Laravel**: Version 8.x or higher
- **MySQL**: Version 5.7 or higher
- **GuzzleHTTP**: For API communication
- **HealthPay Account**: Active HealthPay merchant account

## Installation

### Step 1: Copy Plugin Files

Copy the entire `HealthPay` directory to your Rocket LMS plugins folder:

```bash
cp -r HealthPay /path/to/rocket-lms/plugins/PaymentChannels/
```

### Step 2: Register Service Provider

Add the HealthPay service provider to your `config/app.php`:

```php
'providers' => [
    // Other providers...
    Plugins\PaymentChannels\HealthPay\HealthPayServiceProvider::class,
],
```

### Step 3: Run Migrations

Execute the database migrations to create required tables:

```bash
php artisan migrate
```

### Step 4: Publish Assets

Publish the plugin assets (optional):

```bash
php artisan vendor:publish --tag=healthpay-assets
php artisan vendor:publish --tag=healthpay-config
```

### Step 5: Clear Cache

Clear application cache:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Configuration

### Admin Settings

1. Navigate to **Admin Panel** → **Financial** → **Payment Gateways** → **HealthPay Settings**
2. Configure the following settings:

#### Basic Settings

- **Status**: Enable or disable the gateway
- **Mode**: Select Sandbox (testing) or Live (production)
- **API Endpoint**: Choose between sandbox and production endpoints

#### API Credentials

- **API Key**: Your HealthPay API key
- **API Secret**: Your HealthPay API secret
- **Webhook Secret**: (Optional) Secret for webhook signature verification

### Getting API Credentials

#### Sandbox Environment

1. Visit: [https://portal.beta.healthpay.tech](https://portal.beta.healthpay.tech)
2. Login with test credentials:
   - Username: `beta.account@healthpay.tech`
   - Password: `BetaAcc@HealthPay2024`
3. Navigate to API Settings
4. Generate API Key and Secret

#### Production Environment

1. Visit: [https://portal.healthpay.tech](https://portal.healthpay.tech)
2. Login with your merchant account
3. Navigate to API Settings
4. Generate production API credentials

## Usage

### For Administrators

#### Testing the Connection

1. Go to HealthPay Settings page
2. Click **Test Connection** button
3. Verify that credentials are valid

#### Monitoring Transactions

The admin dashboard displays:
- Total successful transactions
- Pending transactions
- Failed transactions
- Total transaction amount

### For Users

1. Add items to cart
2. Proceed to checkout
3. Select **HealthPay** as payment method
4. Click **Pay Now**
5. You will be redirected to HealthPay payment page
6. Complete payment using your HealthPay wallet
7. Return to Rocket LMS upon completion

## API Endpoints

### Admin Routes

- `GET /admin/healthpay/settings` - View settings page
- `POST /admin/healthpay/settings` - Update settings
- `POST /admin/healthpay/test-connection` - Test API connection

### Payment Routes

- `POST /payments/healthpay/pay` - Initiate payment
- `GET /payments/healthpay/return` - Return URL after payment
- `POST /payments/healthpay/callback` - Callback notification
- `POST /payments/healthpay/webhook` - Webhook endpoint

## Database Schema

### healthpay_settings

Stores plugin configuration:

- `id` - Primary key
- `enabled` - Gateway status
- `mode` - Operating mode (sandbox/live)
- `api_key` - API key
- `api_secret` - API secret
- `api_endpoint` - Endpoint selection
- `webhook_secret` - Webhook verification secret
- `settings` - Additional settings (JSON)
- `last_tested_at` - Last credential test timestamp
- `credentials_valid` - Credential validation status

### healthpay_transactions

Logs all transactions:

- `id` - Primary key
- `order_id` - Reference to orders table
- `user_id` - Reference to users table
- `transaction_id` - HealthPay transaction ID
- `reference_id` - Order reference ID
- `amount` - Transaction amount
- `currency` - Currency code (EGP)
- `status` - Transaction status
- `type` - Transaction type
- `description` - Transaction description
- `response_data` - Full API response
- `payment_url` - HealthPay payment URL
- `webhook_signature` - Webhook signature
- `completed_at` - Completion timestamp

## GraphQL API Operations

### Create Payment Request

```graphql
mutation CreatePaymentRequest($input: PaymentRequestInput!) {
    createPaymentRequest(input: $input) {
        id
        status
        amount
        referenceId
        paymentUrl
    }
}
```

### Check Transaction Status

```graphql
query GetTransaction($id: ID!) {
    transaction(id: $id) {
        id
        status
        amount
        currency
        referenceId
    }
}
```

### Get User Balance

```graphql
query GetUserBalance($userId: ID!) {
    user(id: $userId) {
        wallet {
            balance
            currency
        }
    }
}
```

## Webhook Integration

### Webhook URL

Configure this URL in your HealthPay portal:

```
https://yourdomain.com/payments/healthpay/webhook
```

### Webhook Events

The plugin handles the following webhook events:

- `payment.success` - Payment completed successfully
- `payment.failed` - Payment failed or cancelled
- `refund.completed` - Refund processed

### Webhook Security

Webhooks are verified using HMAC SHA256 signature:

```php
$signature = hash_hmac('sha256', $payload, $webhookSecret);
```

## Troubleshooting

### Common Issues

#### 1. Connection Test Fails

**Problem**: API credentials are invalid

**Solution**:
- Verify API key and secret are correct
- Ensure you're using the correct endpoint (sandbox/production)
- Check that your HealthPay account is active

#### 2. Payment Redirect Fails

**Problem**: Payment URL not generated

**Solution**:
- Check API credentials
- Verify order amount is valid
- Review application logs for errors

#### 3. Webhook Not Received

**Problem**: Payment status not updating

**Solution**:
- Verify webhook URL is configured in HealthPay portal
- Check webhook secret is correct
- Ensure your server is accessible from HealthPay servers

#### 4. Database Migration Errors

**Problem**: Tables not created

**Solution**:
```bash
php artisan migrate:fresh
php artisan migrate --path=/plugins/PaymentChannels/HealthPay/Migrations
```

## Testing

### Sandbox Testing

1. Enable sandbox mode
2. Use sandbox API credentials
3. Test payment flow:
   - Create test order
   - Initiate payment
   - Complete payment in sandbox
   - Verify order status updates

### Test Credentials

**Sandbox Portal**:
- URL: https://portal.beta.healthpay.tech
- Username: beta.account@healthpay.tech
- Password: BetaAcc@HealthPay2024

## Security Considerations

1. **API Credentials**: Store securely, never commit to version control
2. **Webhook Signature**: Always verify webhook signatures
3. **HTTPS**: Use HTTPS for all payment-related pages
4. **Input Validation**: Validate all user inputs
5. **Error Handling**: Don't expose sensitive information in error messages

## Support

### HealthPay Support

- **Portal**: https://portal.beta.healthpay.tech
- **Documentation**: Contact HealthPay for API documentation

### Rocket LMS Support

- **Documentation**: Check plugin bundle documentation
- **Community**: CodeCanyon support forum

## Changelog

### Version 1.0.0 (2025-10-15)

- Initial release
- GraphQL API integration
- Wallet-based payments
- Admin settings interface
- Transaction logging
- Webhook support
- Sandbox and live modes
- Comprehensive error handling

## License

This plugin is part of the Universal Plugins Bundle for Rocket LMS. Please refer to your license agreement for usage terms.

## Credits

**Developed by**: HealthFlow  
**For**: Rocket LMS  
**API Provider**: HealthPay  
**Version**: 1.0.0  
**Release Date**: October 15, 2025

## Contributing

For bug reports, feature requests, or contributions, please contact the development team.

---

**Note**: This plugin requires an active HealthPay merchant account. Contact HealthPay to set up your account before using this plugin in production.

