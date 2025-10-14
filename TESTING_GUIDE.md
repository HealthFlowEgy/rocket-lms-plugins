# HealthPay Integration Testing Guide
## Rocket LMS - Backend & Mobile App

---

## Overview

This document provides comprehensive testing procedures for the HealthPay payment gateway integration across both the Laravel backend and Flutter mobile app.

---

## Pre-Testing Checklist

### Backend (Laravel)

- [ ] Database migrations run successfully
- [ ] HealthPay plugin installed and activated
- [ ] API credentials configured in admin panel
- [ ] Webhook secret configured
- [ ] Routes registered correctly
- [ ] Logs directory writable

### Mobile App (Flutter)

- [ ] Dependencies installed (`flutter pub get`)
- [ ] Deep links configured (Android & iOS)
- [ ] API base URL updated
- [ ] App builds without errors
- [ ] Test device/emulator ready

---

## Backend Testing

### 1. Database Integration Test

**Test:** Verify settings are saved to database

```bash
# Access Laravel tinker
php artisan tinker

# Test setting save
$setting = new \Plugins\PaymentChannels\HealthPay\Models\HealthPaySetting();
$setting->settings = json_encode([
    'mode' => 'sandbox',
    'api_key' => 'test_key',
    'api_secret' => 'test_secret',
    'enabled' => true
]);
$setting->save();

# Verify
$setting = \Plugins\PaymentChannels\HealthPay\Models\HealthPaySetting::first();
print_r(json_decode($setting->settings, true));
```

**Expected Result:** Settings saved and retrieved successfully

---

### 2. Transaction Logging Test

**Test:** Verify transactions are logged

```bash
# In tinker
$transaction = new \Plugins\PaymentChannels\HealthPay\Models\HealthPayTransaction();
$transaction->order_id = 1;
$transaction->user_id = 1;
$transaction->transaction_id = 'test_txn_123';
$transaction->amount = 299.99;
$transaction->currency = 'EGP';
$transaction->status = 'pending';
$transaction->request_data = json_encode(['test' => 'data']);
$transaction->response_data = json_encode(['test' => 'response']);
$transaction->save();

# Verify
$txn = \Plugins\PaymentChannels\HealthPay\Models\HealthPayTransaction::first();
echo $txn->transaction_id;
```

**Expected Result:** Transaction logged successfully

---

### 3. API Endpoint Tests

#### Test 1: Admin Settings Page

```bash
# Access in browser
https://yourdomain.com/admin/healthpay/settings
```

**Expected Result:**
- Settings form displayed
- Current settings loaded
- Test connection button visible

#### Test 2: Save Settings

```bash
# Submit settings form with:
- Mode: Sandbox
- API Key: your_test_key
- API Secret: your_test_secret
- Enabled: Yes
```

**Expected Result:**
- Success message displayed
- Settings saved to database
- Page redirects back to settings

#### Test 3: Test Connection

```bash
# Click "Test Connection" button
```

**Expected Result:**
- AJAX request sent
- Response received (success or error)
- Message displayed to user

---

### 4. Payment Flow Test

#### Test 1: Initiate Payment

```bash
# Create test order first
POST /api/v1/orders
{
  "course_id": 1,
  "amount": 299.99,
  "user_id": 1
}

# Then initiate payment
POST /api/v1/payments/healthpay/initiate
{
  "order_id": "ORDER_ID_FROM_ABOVE",
  "amount": 299.99,
  "currency": "EGP",
  "description": "Test Payment"
}
```

**Expected Result:**
```json
{
  "success": true,
  "data": {
    "transaction_id": "txn_...",
    "payment_url": "https://...",
    "status": "pending"
  }
}
```

**Verify:**
- [ ] Transaction logged in database
- [ ] Order updated with reference_id
- [ ] Payment URL returned

#### Test 2: Payment Callback

```bash
# Simulate callback from HealthPay
GET /healthpay/callback?transaction_id=txn_123&reference_id=ORDER_ID&status=success
```

**Expected Result:**
- Order status updated to 'paid'
- Accounting entry created
- Transaction status updated
- User redirected to success page

**Verify in Database:**
```sql
SELECT * FROM healthpay_transactions WHERE transaction_id = 'txn_123';
SELECT * FROM orders WHERE id = ORDER_ID;
SELECT * FROM accounting WHERE store_id = ORDER_ID;
```

#### Test 3: Webhook Handling

```bash
# Send webhook POST request
POST /healthpay/webhook
Headers:
  X-HealthPay-Signature: HMAC_SIGNATURE
Body:
{
  "event": "payment.success",
  "referenceId": "ORDER_ID",
  "transactionId": "txn_123",
  "amount": 299.99,
  "status": "SUCCESS"
}
```

**Expected Result:**
- Signature verified
- Event processed
- Order updated
- HTTP 200 response

**Check Logs:**
```bash
tail -f storage/logs/laravel.log | grep HealthPay
```

---

### 5. Error Handling Tests

#### Test 1: Invalid API Credentials

```bash
# Set invalid credentials in admin panel
# Try to initiate payment
```

**Expected Result:**
- Error message displayed
- Error logged
- User-friendly error shown

#### Test 2: Network Timeout

```bash
# Temporarily block HealthPay API
# Try to initiate payment
```

**Expected Result:**
- Timeout error caught
- User notified
- Transaction marked as failed

#### Test 3: Invalid Webhook Signature

```bash
# Send webhook with wrong signature
POST /healthpay/webhook
Headers:
  X-HealthPay-Signature: invalid_signature
```

**Expected Result:**
- HTTP 401 Unauthorized
- Warning logged
- Request rejected

---

## Mobile App Testing

### 1. Build and Run Tests

```bash
# Clean and get dependencies
flutter clean
flutter pub get

# Run on Android
flutter run -d android

# Run on iOS
flutter run -d ios
```

**Expected Result:** App builds and runs without errors

---

### 2. Payment Gateway Screen Test

**Steps:**
1. Navigate to a course
2. Click "Buy Now" or "Enroll"
3. Verify Payment Gateway Screen opens

**Verify:**
- [ ] Order summary displayed correctly
- [ ] Amount shown in EGP
- [ ] HealthPay option visible
- [ ] Wallet option visible (if implemented)
- [ ] UI is responsive

---

### 3. WebView Payment Flow Test

**Steps:**
1. Select HealthPay payment method
2. Wait for WebView to load
3. Complete payment on HealthPay page
4. Wait for callback

**Verify:**
- [ ] Loading indicator shown during initialization
- [ ] Payment URL loaded in WebView
- [ ] JavaScript enabled
- [ ] Navigation works correctly
- [ ] Deep link callback triggered
- [ ] Verification request sent
- [ ] Result screen displayed

**Check Logs:**
```bash
# Android
adb logcat | grep Flutter

# iOS
flutter logs
```

---

### 4. Deep Link Test

#### Android

```bash
# Test success deep link
adb shell am start -W -a android.intent.action.VIEW \
  -d "rocketlms://payment/success?transaction_id=test123&order_id=456" \
  com.your.package.name

# Test cancel deep link
adb shell am start -W -a android.intent.action.VIEW \
  -d "rocketlms://payment/cancel?transaction_id=test123" \
  com.your.package.name
```

**Expected Result:**
- App opens
- Correct screen displayed
- Parameters parsed correctly

#### iOS

```bash
# Test on simulator
xcrun simctl openurl booted "rocketlms://payment/success?transaction_id=test123&order_id=456"

# Test on device (requires app installed)
# Use Safari to open: rocketlms://payment/success?transaction_id=test123&order_id=456
```

**Expected Result:**
- App opens
- Correct screen displayed
- Parameters parsed correctly

---

### 5. Payment Result Screen Test

**Test Success Scenario:**

**Steps:**
1. Complete successful payment
2. Verify result screen

**Verify:**
- [ ] Success icon displayed (green checkmark)
- [ ] "Payment Successful" title
- [ ] Transaction details shown
- [ ] Order ID correct
- [ ] Amount correct
- [ ] "Go to My Courses" button works
- [ ] "Download Receipt" button visible

**Test Failure Scenario:**

**Steps:**
1. Cancel or fail payment
2. Verify result screen

**Verify:**
- [ ] Error icon displayed (red X)
- [ ] "Payment Failed" title
- [ ] Error message shown
- [ ] "Try Again" button works
- [ ] "Back to Home" button works

---

### 6. Error Handling Tests

#### Test 1: Network Failure

**Steps:**
1. Turn off internet
2. Try to initiate payment

**Expected Result:**
- Error message displayed
- User can retry
- No crash

#### Test 2: API Timeout

**Steps:**
1. Slow down network (use Charles Proxy or similar)
2. Initiate payment

**Expected Result:**
- Timeout after 30 seconds
- Error message shown
- User can retry

#### Test 3: Invalid Response

**Steps:**
1. Mock API to return invalid JSON
2. Initiate payment

**Expected Result:**
- Error caught
- User-friendly message shown
- No crash

---

## Integration Testing

### End-to-End Payment Flow

**Scenario:** User purchases a course

**Steps:**
1. User browses courses
2. Selects a course
3. Clicks "Buy Now"
4. Payment Gateway Screen opens
5. Selects HealthPay
6. WebView opens with payment page
7. User enters payment details
8. Payment processed by HealthPay
9. Deep link callback to app
10. App verifies payment with backend
11. Success screen displayed
12. User navigates to "My Courses"
13. Course appears in user's library

**Verify at Each Step:**
- [ ] UI responsive
- [ ] Data passed correctly
- [ ] API calls successful
- [ ] Database updated
- [ ] Logs recorded
- [ ] User experience smooth

---

## Performance Testing

### Backend

**Test 1: Concurrent Payments**

```bash
# Use Apache Bench
ab -n 100 -c 10 -p payment_data.json -T application/json \
  https://yourdomain.com/api/v1/payments/healthpay/initiate
```

**Expected Result:**
- All requests processed
- No database deadlocks
- Response time < 2 seconds

**Test 2: Database Query Performance**

```sql
EXPLAIN SELECT * FROM healthpay_transactions 
WHERE user_id = 1 ORDER BY created_at DESC LIMIT 10;
```

**Expected Result:**
- Index used
- Query time < 100ms

### Mobile App

**Test 1: WebView Load Time**

**Measure:**
- Time from button click to WebView display
- Time to load payment page

**Expected Result:**
- < 3 seconds to initialize
- < 5 seconds to load page

**Test 2: Memory Usage**

```bash
# Android
adb shell dumpsys meminfo com.your.package.name

# iOS
# Use Xcode Instruments
```

**Expected Result:**
- Memory usage < 150MB
- No memory leaks

---

## Security Testing

### Backend

**Test 1: SQL Injection**

```bash
# Try SQL injection in order_id
POST /api/v1/payments/healthpay/initiate
{
  "order_id": "1' OR '1'='1",
  "amount": 299.99
}
```

**Expected Result:**
- Request rejected or sanitized
- No database error
- No data leaked

**Test 2: XSS Attack**

```bash
# Try XSS in description
POST /api/v1/payments/healthpay/initiate
{
  "order_id": "123",
  "description": "<script>alert('XSS')</script>"
}
```

**Expected Result:**
- Script tags escaped
- No script execution
- Data sanitized

**Test 3: CSRF Protection**

```bash
# Try request without CSRF token
POST /admin/healthpay/settings
```

**Expected Result:**
- HTTP 419 (CSRF token mismatch)
- Request rejected

### Mobile App

**Test 1: API Key Security**

**Verify:**
- [ ] API key not hardcoded
- [ ] Stored in secure storage
- [ ] Not logged in console
- [ ] Not in version control

**Test 2: HTTPS Enforcement**

**Verify:**
- [ ] All API calls use HTTPS
- [ ] Certificate validation enabled
- [ ] No mixed content

---

## Regression Testing

### After Code Changes

**Test Suite:**
1. [ ] Database migrations still work
2. [ ] Settings save/load correctly
3. [ ] Payment initiation works
4. [ ] Callbacks processed correctly
5. [ ] Webhooks handled properly
6. [ ] Mobile app builds successfully
7. [ ] Deep links still work
8. [ ] UI displays correctly
9. [ ] Error handling works
10. [ ] Logs recorded properly

---

## User Acceptance Testing (UAT)

### Test Scenarios

**Scenario 1: Happy Path**
- User successfully purchases a course
- Payment completes
- Course access granted

**Scenario 2: Payment Cancellation**
- User starts payment
- Cancels before completing
- Order remains unpaid
- User can retry

**Scenario 3: Payment Failure**
- Payment declined by bank
- User sees error message
- Can try different payment method
- Order not marked as paid

**Scenario 4: Network Issues**
- Payment initiated
- Network drops
- User sees error
- Can retry when network returns

---

## Monitoring and Logging

### What to Monitor

**Backend:**
- Payment success rate
- Average processing time
- Error rate
- Webhook delivery rate

**Mobile App:**
- Crash rate
- Payment completion rate
- WebView load time
- Deep link success rate

### Log Analysis

```bash
# Check for errors
grep "ERROR" storage/logs/laravel.log | grep HealthPay

# Check payment success rate
grep "HealthPay transaction logged" storage/logs/laravel.log | grep "success" | wc -l

# Check failed payments
grep "HealthPay transaction logged" storage/logs/laravel.log | grep "failed" | wc -l
```

---

## Troubleshooting Guide

### Common Issues

#### Issue 1: "Payment URL not received"

**Cause:** HealthPay API error or invalid credentials

**Solution:**
1. Check API credentials
2. Verify network connectivity
3. Check HealthPay API status
4. Review backend logs

#### Issue 2: "Deep link not working"

**Cause:** Incorrect configuration

**Solution:**
1. Verify AndroidManifest.xml
2. Check Info.plist (iOS)
3. Test deep link manually
4. Rebuild app

#### Issue 3: "Payment verification failed"

**Cause:** Backend API error or timeout

**Solution:**
1. Check backend logs
2. Verify API endpoint
3. Check network connectivity
4. Increase timeout

---

## Test Report Template

```markdown
# HealthPay Integration Test Report

**Date:** YYYY-MM-DD
**Tester:** Name
**Environment:** Production/Staging/Development

## Backend Tests
- [ ] Database Integration: PASS/FAIL
- [ ] Transaction Logging: PASS/FAIL
- [ ] API Endpoints: PASS/FAIL
- [ ] Payment Flow: PASS/FAIL
- [ ] Error Handling: PASS/FAIL

## Mobile App Tests
- [ ] Build & Run: PASS/FAIL
- [ ] Payment Gateway Screen: PASS/FAIL
- [ ] WebView Flow: PASS/FAIL
- [ ] Deep Links: PASS/FAIL
- [ ] Result Screen: PASS/FAIL
- [ ] Error Handling: PASS/FAIL

## Integration Tests
- [ ] End-to-End Flow: PASS/FAIL

## Performance Tests
- [ ] Backend Performance: PASS/FAIL
- [ ] Mobile Performance: PASS/FAIL

## Security Tests
- [ ] Backend Security: PASS/FAIL
- [ ] Mobile Security: PASS/FAIL

## Issues Found
1. Issue description
2. Issue description

## Recommendations
1. Recommendation
2. Recommendation

## Sign-off
- [ ] All critical tests passed
- [ ] Ready for production
```

---

## Conclusion

This testing guide ensures comprehensive validation of the HealthPay integration across all components. Follow this guide before deploying to production.

For issues or questions, consult the troubleshooting guide or contact the development team.

