class PaymentResponse {
  final String transactionId;
  final String paymentUrl;
  final String status;
  final double amount;
  final String currency;
  final DateTime? createdAt;
  final Map<String, dynamic>? data;

  PaymentResponse({
    required this.transactionId,
    required this.paymentUrl,
    required this.status,
    required this.amount,
    required this.currency,
    this.createdAt,
    this.data,
  });

  factory PaymentResponse.fromJson(Map<String, dynamic> json) {
    return PaymentResponse(
      transactionId: json['transaction_id'] ?? '',
      paymentUrl: json['payment_url'] ?? '',
      status: json['status'] ?? 'pending',
      amount: (json['amount'] ?? 0.0).toDouble(),
      currency: json['currency'] ?? 'EGP',
      createdAt: json['created_at'] != null 
        ? DateTime.parse(json['created_at']) 
        : null,
      data: json['data'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'transaction_id': transactionId,
      'payment_url': paymentUrl,
      'status': status,
      'amount': amount,
      'currency': currency,
      'created_at': createdAt?.toIso8601String(),
      'data': data,
    };
  }
}

