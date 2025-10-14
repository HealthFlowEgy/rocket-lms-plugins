# HealthPay Flutter Integration Guide
## Rocket LMS Mobile App - Android & iOS

---

## Overview

This document provides complete implementation details for the HealthPay payment gateway integration in the Rocket LMS Flutter mobile app.

### Architecture

The Flutter app communicates with the Laravel backend via REST API. Payment processing is handled through the web API with mobile-optimized UI components.

---

## Files Added

### Models (`lib/features/payment/models/`)
- `payment_request.dart` - Payment request data model
- `payment_response.dart` - Payment response data model
- `payment_status.dart` - Payment status enum and model

### Services (`lib/features/payment/services/`)
- `healthpay_service.dart` - HealthPay API service layer

### Screens (`lib/features/payment/screens/`)
- `payment_gateway_screen.dart` - Payment method selection
- `healthpay_webview_screen.dart` - HealthPay payment WebView
- `payment_result_screen.dart` - Payment success/failure result

---

## Dependencies

All required dependencies are already present in `pubspec.yaml`:

```yaml
dependencies:
  webview_flutter: ^4.4.2      # ✅ Already present
  http: ^1.1.2                  # ✅ Already present
  provider: ^6.1.1              # ✅ Already present
  url_launcher: ^6.1.12         # ✅ Already present
  lottie: ^3.0.0                # ✅ Already present
  shimmer: ^3.0.0               # ✅ Already present
  cached_network_image: ^3.3.1  # ✅ Already present
```

No additional dependencies need to be added!

---

## Platform Configuration

### Android Configuration

#### 1. Update `android/app/src/main/AndroidManifest.xml`

Add deep link intent filter:

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">
    <application>
        <activity
            android:name=".MainActivity"
            android:launchMode="singleTop">
            
            <!-- Existing intent filters -->
            <intent-filter>
                <action android:name="android.intent.action.MAIN"/>
                <category android:name="android.intent.category.LAUNCHER"/>
            </intent-filter>
            
            <!-- Add HealthPay deep link -->
            <intent-filter android:autoVerify="true">
                <action android:name="android.intent.action.VIEW" />
                <category android:name="android.intent.category.DEFAULT" />
                <category android:name="android.intent.category.BROWSABLE" />
                <data
                    android:scheme="rocketlms"
                    android:host="payment" />
            </intent-filter>
        </activity>
    </application>
    
    <!-- Internet permission (should already exist) -->
    <uses-permission android:name="android.permission.INTERNET" />
</manifest>
```

#### 2. Update `android/app/build.gradle`

Ensure minimum SDK version:

```gradle
android {
    defaultConfig {
        minSdkVersion 21  // Required for WebView
        targetSdkVersion 34
    }
}
```

### iOS Configuration

#### 1. Update `ios/Runner/Info.plist`

Add URL schemes:

```xml
<dict>
    <!-- Existing keys -->
    
    <!-- Add URL Types for deep linking -->
    <key>CFBundleURLTypes</key>
    <array>
        <dict>
            <key>CFBundleTypeRole</key>
            <string>Editor</string>
            <key>CFBundleURLName</key>
            <string>com.rocketlms.payment</string>
            <key>CFBundleURLSchemes</key>
            <array>
                <string>rocketlms</string>
            </array>
        </dict>
    </array>
    
    <!-- Allow HTTP connections (for development only) -->
    <key>NSAppTransportSecurity</key>
    <dict>
        <key>NSAllowsArbitraryLoads</key>
        <true/>
    </dict>
</dict>
```

#### 2. Update `ios/Podfile`

Ensure iOS version:

```ruby
platform :ios, '12.0'  # Required for WebView
```

---

## API Endpoints Required

The Flutter app expects these endpoints from your Laravel backend:

### 1. Initiate Payment
```
POST /api/v1/payments/healthpay/initiate
```

**Request:**
```json
{
  "order_id": "12345",
  "amount": 299.99,
  "currency": "EGP",
  "description": "Payment for Course Name",
  "return_url": "rocketlms://payment/success",
  "cancel_url": "rocketlms://payment/cancel",
  "metadata": {
    "course_title": "Course Name",
    "source": "mobile_app"
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "transaction_id": "txn_123456",
    "payment_url": "https://pay.healthpay.tech/checkout/...",
    "status": "pending",
    "amount": 299.99,
    "currency": "EGP",
    "created_at": "2025-10-15T10:30:00Z"
  }
}
```

### 2. Verify Payment
```
POST /api/v1/payments/healthpay/verify
```

**Request:**
```json
{
  "transaction_id": "txn_123456",
  "order_id": "12345"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "order_id": "12345",
    "transaction_id": "txn_123456",
    "status": "success",
    "amount": 299.99,
    "timestamp": "2025-10-15T10:35:00Z"
  }
}
```

### 3. Check Status
```
GET /api/v1/payments/healthpay/status/{transaction_id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "order_id": "12345",
    "transaction_id": "txn_123456",
    "status": "success",
    "amount": 299.99,
    "timestamp": "2025-10-15T10:35:00Z"
  }
}
```

---

## Usage Example

### Integrate into Course Purchase Flow

```dart
// In your course details screen or checkout screen
import 'package:rocket_lms/features/payment/screens/payment_gateway_screen.dart';

// When user clicks "Buy Now" or "Checkout"
void _proceedToPayment() {
  Navigator.push(
    context,
    MaterialPageRoute(
      builder: (context) => PaymentGatewayScreen(
        orderId: order.id.toString(),
        amount: course.price,
        courseTitle: course.title,
      ),
    ),
  );
}
```

---

## Payment Flow

### User Journey

1. **User selects course** → Clicks "Buy Now"
2. **Payment Gateway Selection** → User sees available payment methods
3. **Selects HealthPay** → WebView opens with HealthPay payment page
4. **Completes payment** → User enters payment details on HealthPay
5. **Callback** → Deep link redirects back to app
6. **Verification** → App verifies payment with backend
7. **Result** → Success or failure screen shown

### Technical Flow

```
Flutter App → Laravel API (initiate) → HealthPay API
     ↓
WebView loads HealthPay payment page
     ↓
User completes payment
     ↓
HealthPay → Deep Link → Flutter App
     ↓
Flutter App → Laravel API (verify) → HealthPay API
     ↓
Result Screen (Success/Failure)
```

---

## Configuration

### Update API Base URL

Edit `lib/features/payment/services/healthpay_service.dart`:

```dart
HealthPayService({
  http.Client? client,
  String? baseUrl,
  String? apiKey,
})  : _client = client ?? http.Client(),
      _baseUrl = baseUrl ?? 'https://yourdomain.com/api/v1',  // ← Change this
      _apiKey = apiKey ?? '';  // ← Get from secure storage
```

### Store API Key Securely

Use `flutter_secure_storage` (already in dependencies):

```dart
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

final storage = FlutterSecureStorage();

// Store API key after login
await storage.write(key: 'api_key', value: userApiKey);

// Retrieve for payment service
final apiKey = await storage.read(key: 'api_key');
final paymentService = HealthPayService(apiKey: apiKey);
```

---

## Testing

### Test Payment Flow

1. **Run the app**
   ```bash
   flutter run
   ```

2. **Navigate to a course** and click "Buy Now"

3. **Select HealthPay** from payment methods

4. **Complete test payment** using sandbox credentials

5. **Verify callback** works correctly

6. **Check result screen** shows correct status

### Test Deep Links

#### Android
```bash
adb shell am start -W -a android.intent.action.VIEW \
  -d "rocketlms://payment/success?transaction_id=test123" \
  com.your.package.name
```

#### iOS
```bash
xcrun simctl openurl booted "rocketlms://payment/success?transaction_id=test123"
```

---

## Troubleshooting

### WebView Not Loading

**Issue:** WebView shows blank screen

**Solutions:**
- Check internet permission in AndroidManifest.xml
- Verify API endpoint is correct
- Check backend logs for errors
- Enable JavaScript in WebView (already enabled)

### Deep Links Not Working

**Issue:** App doesn't open after payment

**Solutions:**
- Verify intent filter in AndroidManifest.xml
- Check URL scheme in Info.plist (iOS)
- Test deep link manually (see Testing section)
- Ensure app is installed and running

### Payment Verification Fails

**Issue:** Payment succeeds but verification fails

**Solutions:**
- Check backend API is accessible
- Verify transaction ID is passed correctly
- Check backend logs for errors
- Ensure webhook is configured in HealthPay portal

---

## Security Best Practices

### 1. API Key Storage
✅ Use `flutter_secure_storage` for API keys
❌ Never hardcode API keys in source code

### 2. HTTPS Only
✅ Use HTTPS for all API calls
❌ Remove `NSAllowsArbitraryLoads` in production

### 3. Certificate Pinning (Optional)
For production, consider implementing certificate pinning:

```dart
import 'package:dio/dio.dart';
import 'package:dio/io.dart';

final dio = Dio();
(dio.httpClientAdapter as IOHttpClientAdapter).onHttpClientCreate = 
  (HttpClient client) {
    client.badCertificateCallback = 
      (X509Certificate cert, String host, int port) => false;
    return client;
  };
```

### 4. Input Validation
Always validate amounts and order IDs before sending to backend

---

## Production Checklist

- [ ] Update API base URL to production
- [ ] Remove `NSAllowsArbitraryLoads` from Info.plist
- [ ] Test on real devices (Android & iOS)
- [ ] Test deep links on both platforms
- [ ] Verify payment flow end-to-end
- [ ] Test error scenarios (network failure, payment decline)
- [ ] Enable ProGuard/R8 for Android
- [ ] Test with production HealthPay credentials
- [ ] Monitor crash reports
- [ ] Set up analytics for payment events

---

## Support

For issues or questions:
- Backend API: Check Laravel logs
- HealthPay API: Contact HealthPay support
- Mobile App: Check Flutter logs with `flutter logs`

---

## Version History

- **v1.0.0** (2025-10-15) - Initial HealthPay integration
  - Payment gateway selection screen
  - WebView payment flow
  - Deep link handling
  - Payment verification
  - Result screens

---

## License

This integration is part of the Rocket LMS project.

