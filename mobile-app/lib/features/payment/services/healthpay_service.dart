import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/payment_request.dart';
import '../models/payment_response.dart';
import '../models/payment_status.dart';

class HealthPayService {
  final http.Client _client;
  final String _baseUrl;
  final String _apiKey;

  HealthPayService({
    http.Client? client,
    String? baseUrl,
    String? apiKey,
  })  : _client = client ?? http.Client(),
        _baseUrl = baseUrl ?? 'https://yourdomain.com/api/v1',
        _apiKey = apiKey ?? '';

  /// Initiate payment with HealthPay
  Future<PaymentResponse> initiatePayment(PaymentRequest request) async {
    try {
      final response = await _client.post(
        Uri.parse('$_baseUrl/payments/healthpay/initiate'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $_apiKey',
        },
        body: json.encode(request.toJson()),
      ).timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          throw Exception('Request timeout. Please try again.');
        },
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        final data = json.decode(response.body);
        
        if (data['success'] == true) {
          return PaymentResponse.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to initiate payment');
        }
      } else {
        final error = json.decode(response.body);
        throw Exception(error['message'] ?? 'Failed to initiate payment');
      }
    } catch (e) {
      throw Exception('Payment initiation failed: ${e.toString()}');
    }
  }

  /// Check payment status
  Future<PaymentStatus> checkPaymentStatus(String transactionId) async {
    try {
      final response = await _client.get(
        Uri.parse('$_baseUrl/payments/healthpay/status/$transactionId'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $_apiKey',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        if (data['success'] == true) {
          return PaymentStatus.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to get payment status');
        }
      } else {
        throw Exception('Failed to check payment status');
      }
    } catch (e) {
      throw Exception('Status check failed: ${e.toString()}');
    }
  }

  /// Verify payment after callback
  Future<PaymentStatus> verifyPayment({
    required String transactionId,
    required String orderId,
  }) async {
    try {
      final response = await _client.post(
        Uri.parse('$_baseUrl/payments/healthpay/verify'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $_apiKey',
        },
        body: json.encode({
          'transaction_id': transactionId,
          'order_id': orderId,
        }),
      ).timeout(const Duration(seconds: 20));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        if (data['success'] == true) {
          return PaymentStatus.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Payment verification failed');
        }
      } else {
        throw Exception('Failed to verify payment');
      }
    } catch (e) {
      throw Exception('Verification failed: ${e.toString()}');
    }
  }

  /// Cancel payment
  Future<bool> cancelPayment(String transactionId) async {
    try {
      final response = await _client.post(
        Uri.parse('$_baseUrl/payments/healthpay/cancel'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $_apiKey',
        },
        body: json.encode({
          'transaction_id': transactionId,
        }),
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }

  void dispose() {
    _client.close();
  }
}

