class PaymentRequest {
  final String orderId;
  final double amount;
  final String currency;
  final String description;
  final String returnUrl;
  final String cancelUrl;
  final Map<String, dynamic>? metadata;

  PaymentRequest({
    required this.orderId,
    required this.amount,
    this.currency = 'EGP',
    required this.description,
    required this.returnUrl,
    required this.cancelUrl,
    this.metadata,
  });

  Map<String, dynamic> toJson() {
    return {
      'order_id': orderId,
      'amount': amount,
      'currency': currency,
      'description': description,
      'return_url': returnUrl,
      'cancel_url': cancelUrl,
      'metadata': metadata,
    };
  }
}

