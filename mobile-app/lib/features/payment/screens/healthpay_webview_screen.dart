import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../services/healthpay_service.dart';
import '../models/payment_request.dart';
import 'payment_result_screen.dart';

class HealthPayWebViewScreen extends StatefulWidget {
  final String orderId;
  final double amount;
  final String courseTitle;

  const HealthPayWebViewScreen({
    Key? key,
    required this.orderId,
    required this.amount,
    required this.courseTitle,
  }) : super(key: key);

  @override
  State<HealthPayWebViewScreen> createState() => _HealthPayWebViewScreenState();
}

class _HealthPayWebViewScreenState extends State<HealthPayWebViewScreen> {
  late final WebViewController _controller;
  final HealthPayService _paymentService = HealthPayService();
  
  bool _isLoading = true;
  String? _errorMessage;
  String? _paymentUrl;
  String? _transactionId;

  @override
  void initState() {
    super.initState();
    _initializePayment();
  }

  Future<void> _initializePayment() async {
    try {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });

      // Create payment request
      final paymentRequest = PaymentRequest(
        orderId: widget.orderId,
        amount: widget.amount,
        currency: 'EGP',
        description: 'Payment for ${widget.courseTitle}',
        returnUrl: 'rocketlms://payment/success',
        cancelUrl: 'rocketlms://payment/cancel',
        metadata: {
          'course_title': widget.courseTitle,
          'source': 'mobile_app',
        },
      );

      // Initiate payment
      final response = await _paymentService.initiatePayment(paymentRequest);
      
      setState(() {
        _paymentUrl = response.paymentUrl;
        _transactionId = response.transactionId;
        _isLoading = false;
      });

      // Initialize WebView
      _initializeWebView();

    } catch (e) {
      setState(() {
        _errorMessage = e.toString();
        _isLoading = false;
      });
    }
  }

  void _initializeWebView() {
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (String url) {
            _handleNavigation(url);
          },
          onPageFinished: (String url) {
            setState(() => _isLoading = false);
          },
          onWebResourceError: (WebResourceError error) {
            _showError('Failed to load payment page: ${error.description}');
          },
        ),
      )
      ..loadRequest(Uri.parse(_paymentUrl!));
  }

  void _handleNavigation(String url) {
    // Handle deep links for success/cancel
    if (url.contains('rocketlms://payment/success') || 
        url.contains('/healthpay/callback')) {
      _verifyAndCompletePayment();
    } else if (url.contains('rocketlms://payment/cancel')) {
      _handlePaymentCancel();
    }
  }

  Future<void> _verifyAndCompletePayment() async {
    try {
      // Show loading
      _showLoadingDialog();

      // Verify payment status
      final status = await _paymentService.verifyPayment(
        transactionId: _transactionId!,
        orderId: widget.orderId,
      );

      // Close loading dialog
      if (mounted) Navigator.of(context).pop();

      // Navigate to result screen
      if (mounted) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(
            builder: (context) => PaymentResultScreen(
              isSuccess: status.isSuccess,
              orderId: widget.orderId,
              transactionId: _transactionId!,
              amount: widget.amount,
              message: status.isSuccess 
                ? 'Payment completed successfully!'
                : status.errorMessage ?? 'Payment failed',
            ),
          ),
        );
      }
    } catch (e) {
      if (mounted) Navigator.of(context).pop();
      _showError('Payment verification failed: ${e.toString()}');
    }
  }

  void _handlePaymentCancel() {
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(
        builder: (context) => PaymentResultScreen(
          isSuccess: false,
          orderId: widget.orderId,
          transactionId: _transactionId ?? '',
          amount: widget.amount,
          message: 'Payment was cancelled',
        ),
      ),
    );
  }

  void _showLoadingDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(
        child: Card(
          child: Padding(
            padding: EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                CircularProgressIndicator(),
                SizedBox(height: 16),
                Text('Verifying payment...'),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
        duration: const Duration(seconds: 5),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('HealthPay Payment'),
        actions: [
          IconButton(
            icon: const Icon(Icons.close),
            onPressed: () {
              _showCancelConfirmation();
            },
          ),
        ],
      ),
      body: Stack(
        children: [
          if (_errorMessage != null)
            _buildErrorView()
          else if (_paymentUrl != null)
            WebViewWidget(controller: _controller)
          else
            const SizedBox.shrink(),
          
          if (_isLoading)
            const Center(
              child: CircularProgressIndicator(),
            ),
        ],
      ),
    );
  }

  Widget _buildErrorView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.error_outline,
              size: 64,
              color: Colors.red,
            ),
            const SizedBox(height: 16),
            Text(
              'Payment Initialization Failed',
              style: Theme.of(context).textTheme.titleLarge,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              _errorMessage ?? 'Unknown error occurred',
              style: Theme.of(context).textTheme.bodyMedium,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _initializePayment,
              child: const Text('Retry'),
            ),
            const SizedBox(height: 12),
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('Cancel'),
            ),
          ],
        ),
      ),
    );
  }

  void _showCancelConfirmation() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancel Payment?'),
        content: const Text('Are you sure you want to cancel this payment?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('No'),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              Navigator.of(context).pop();
            },
            child: const Text('Yes, Cancel'),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _paymentService.dispose();
    super.dispose();
  }
}

