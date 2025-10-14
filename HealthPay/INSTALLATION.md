# HealthPay Plugin Installation Guide

This guide provides step-by-step instructions for installing and configuring the HealthPay payment gateway plugin for Rocket LMS.

## Prerequisites

Before you begin, ensure you have:

- ✅ Rocket LMS version 2.0.0 or higher installed
- ✅ PHP 8.0 or higher
- ✅ MySQL 5.7 or higher
- ✅ Composer installed
- ✅ Admin access to your Rocket LMS installation
- ✅ HealthPay merchant account (or sandbox access)

## Installation Steps

### Step 1: Backup Your System

**Important**: Always backup your database and files before installing new plugins.

```bash
# Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Backup files
tar -czf rocket_lms_backup_$(date +%Y%m%d).tar.gz /path/to/rocket-lms/
```

### Step 2: Upload Plugin Files

#### Option A: Manual Upload

1. Download the HealthPay plugin package
2. Extract the ZIP file
3. Upload the `HealthPay` folder to your Rocket LMS installation:

```
/path/to/rocket-lms/plugins/PaymentChannels/HealthPay/
```

#### Option B: Using Command Line

```bash
cd /path/to/rocket-lms/plugins/PaymentChannels/
git clone <repository-url> HealthPay
# or
cp -r /path/to/HealthPay ./
```

### Step 3: Set Correct Permissions

Ensure the plugin directory has correct permissions:

```bash
cd /path/to/rocket-lms/plugins/PaymentChannels/
chmod -R 755 HealthPay/
chown -R www-data:www-data HealthPay/
```

### Step 4: Install Dependencies

If the plugin requires additional PHP packages:

```bash
cd /path/to/rocket-lms/
composer require guzzlehttp/guzzle
```

### Step 5: Register Service Provider

#### For Laravel 8.x and above:

Add to `config/app.php`:

```php
'providers' => [
    // ... other providers
    Plugins\PaymentChannels\HealthPay\HealthPayServiceProvider::class,
],
```

#### For Auto-Discovery:

If your Laravel version supports package auto-discovery, this step may be automatic.

### Step 6: Run Database Migrations

Execute the migrations to create required database tables:

```bash
cd /path/to/rocket-lms/
php artisan migrate
```

You should see output similar to:

```
Migrating: 2025_10_15_000001_create_healthpay_settings_table
Migrated:  2025_10_15_000001_create_healthpay_settings_table (XX.XXms)
Migrating: 2025_10_15_000002_create_healthpay_transactions_table
Migrated:  2025_10_15_000002_create_healthpay_transactions_table (XX.XXms)
```

### Step 7: Publish Assets (Optional)

Publish plugin assets to public directory:

```bash
php artisan vendor:publish --tag=healthpay-assets
php artisan vendor:publish --tag=healthpay-config
php artisan vendor:publish --tag=healthpay-views
```

### Step 8: Clear Application Cache

Clear all caches to ensure the plugin is recognized:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Step 9: Verify Installation

Check that the plugin is properly installed:

```bash
php artisan route:list | grep healthpay
```

You should see routes like:
```
POST   | admin/healthpay/settings
POST   | payments/healthpay/pay
GET    | payments/healthpay/return
POST   | payments/healthpay/callback
POST   | payments/healthpay/webhook
```

## Configuration

### Step 1: Access Admin Panel

1. Log in to your Rocket LMS admin panel
2. Navigate to: **Financial** → **Payment Gateways** → **HealthPay**

### Step 2: Obtain HealthPay Credentials

#### For Sandbox Testing:

1. Visit: https://portal.beta.healthpay.tech
2. Login with test credentials:
   - Username: `beta.account@healthpay.tech`
   - Password: `BetaAcc@HealthPay2024`
3. Navigate to **API Settings** or **Developer** section
4. Generate or copy your API Key and API Secret

#### For Production:

1. Visit: https://portal.healthpay.tech
2. Login with your merchant account
3. Navigate to **API Settings**
4. Generate production API credentials
5. **Important**: Keep these credentials secure!

### Step 3: Configure Plugin Settings

In the HealthPay settings page, enter:

1. **Status**: Set to "Enabled"
2. **Mode**: Select "Sandbox" for testing, "Live" for production
3. **API Endpoint**: Select "Sandbox" or "Production"
4. **API Key**: Enter your HealthPay API Key
5. **API Secret**: Enter your HealthPay API Secret
6. **Webhook Secret**: (Optional) Enter webhook verification secret

### Step 4: Test Connection

1. Click the **Test Connection** button
2. Verify you see a success message
3. If the test fails, double-check your credentials

### Step 5: Configure Webhook URL

In your HealthPay portal:

1. Navigate to **Webhook Settings**
2. Add your webhook URL:
   ```
   https://yourdomain.com/payments/healthpay/webhook
   ```
3. Select events to receive:
   - ✅ payment.success
   - ✅ payment.failed
   - ✅ refund.completed
4. Save the webhook configuration

## Testing

### Test Payment Flow

1. **Enable Sandbox Mode**:
   - Set Mode to "Sandbox"
   - Set API Endpoint to "Sandbox"
   - Save settings

2. **Create Test Order**:
   - Log in as a regular user
   - Add a course to cart
   - Proceed to checkout

3. **Select HealthPay**:
   - Choose HealthPay as payment method
   - Click "Pay Now"

4. **Complete Payment**:
   - You'll be redirected to HealthPay sandbox
   - Complete the test payment
   - Verify you're redirected back to Rocket LMS

5. **Verify Order Status**:
   - Check that order status is "Paid"
   - Verify transaction appears in admin panel
   - Check accounting records

## Going Live

### Pre-Launch Checklist

- [ ] All sandbox tests completed successfully
- [ ] Production API credentials obtained
- [ ] Webhook URL configured in production portal
- [ ] SSL certificate installed (HTTPS enabled)
- [ ] Backup created
- [ ] Terms and conditions updated

### Switch to Production

1. **Update Settings**:
   - Mode: Change to "Live"
   - API Endpoint: Change to "Production"
   - API Key: Enter production key
   - API Secret: Enter production secret

2. **Test with Real Transaction**:
   - Create a small test order
   - Complete payment with real funds
   - Verify everything works correctly

3. **Monitor Transactions**:
   - Check admin dashboard regularly
   - Monitor transaction logs
   - Verify webhook notifications

## Troubleshooting

### Issue: Plugin Not Appearing in Admin

**Solution**:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Issue: Migration Errors

**Solution**:
```bash
# Check migration status
php artisan migrate:status

# Rollback and re-run
php artisan migrate:rollback
php artisan migrate
```

### Issue: Routes Not Working

**Solution**:
```bash
php artisan route:clear
php artisan route:cache
```

### Issue: Permission Denied Errors

**Solution**:
```bash
cd /path/to/rocket-lms/
chmod -R 755 plugins/
chown -R www-data:www-data plugins/
```

### Issue: API Connection Fails

**Checklist**:
- ✅ Verify API credentials are correct
- ✅ Check you're using correct endpoint (sandbox/production)
- ✅ Ensure server can make outbound HTTPS requests
- ✅ Check firewall settings
- ✅ Verify HealthPay service is operational

## Uninstallation

If you need to remove the plugin:

### Step 1: Disable Gateway

1. Go to HealthPay settings
2. Set Status to "Disabled"
3. Save settings

### Step 2: Backup Data

```bash
# Export transaction data
mysqldump -u username -p database_name healthpay_transactions > healthpay_backup.sql
```

### Step 3: Remove Database Tables

```bash
php artisan migrate:rollback --path=/plugins/PaymentChannels/HealthPay/Migrations
```

### Step 4: Remove Files

```bash
rm -rf /path/to/rocket-lms/plugins/PaymentChannels/HealthPay/
```

### Step 5: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## Support

### Getting Help

- **HealthPay API Issues**: Contact HealthPay support
- **Plugin Issues**: Check documentation or contact developer
- **Rocket LMS Issues**: Visit CodeCanyon support forum

### Useful Commands

```bash
# Check Laravel version
php artisan --version

# Check installed packages
composer show

# View logs
tail -f storage/logs/laravel.log

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

## Security Best Practices

1. **Never commit credentials** to version control
2. **Use environment variables** for sensitive data
3. **Enable HTTPS** for all payment pages
4. **Keep plugin updated** to latest version
5. **Monitor transaction logs** regularly
6. **Backup database** before updates
7. **Use strong webhook secrets**
8. **Limit admin access** to settings

## Next Steps

After successful installation:

1. ✅ Test thoroughly in sandbox mode
2. ✅ Review transaction logs
3. ✅ Configure email notifications
4. ✅ Update user documentation
5. ✅ Train staff on new payment method
6. ✅ Monitor first few live transactions closely

---

**Installation Complete!** 🎉

Your HealthPay payment gateway is now ready to accept payments. If you encounter any issues, refer to the troubleshooting section or contact support.

