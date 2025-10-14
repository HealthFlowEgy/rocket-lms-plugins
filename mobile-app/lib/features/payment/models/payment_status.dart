enum PaymentStatusType {
  pending,
  processing,
  success,
  failed,
  cancelled,
  refunded,
}

class PaymentStatus {
  final String orderId;
  final String transactionId;
  final PaymentStatusType status;
  final double amount;
  final String? errorMessage;
  final DateTime timestamp;

  PaymentStatus({
    required this.orderId,
    required this.transactionId,
    required this.status,
    required this.amount,
    this.errorMessage,
    required this.timestamp,
  });

  factory PaymentStatus.fromJson(Map<String, dynamic> json) {
    return PaymentStatus(
      orderId: json['order_id'] ?? '',
      transactionId: json['transaction_id'] ?? '',
      status: _parseStatus(json['status']),
      amount: (json['amount'] ?? 0.0).toDouble(),
      errorMessage: json['error_message'],
      timestamp: json['timestamp'] != null
          ? DateTime.parse(json['timestamp'])
          : DateTime.now(),
    );
  }

  static PaymentStatusType _parseStatus(String? status) {
    switch (status?.toLowerCase()) {
      case 'success':
      case 'completed':
        return PaymentStatusType.success;
      case 'failed':
        return PaymentStatusType.failed;
      case 'cancelled':
        return PaymentStatusType.cancelled;
      case 'refunded':
        return PaymentStatusType.refunded;
      case 'processing':
        return PaymentStatusType.processing;
      default:
        return PaymentStatusType.pending;
    }
  }

  bool get isSuccess => status == PaymentStatusType.success;
  bool get isFailed => status == PaymentStatusType.failed;
  bool get isPending => status == PaymentStatusType.pending;
}

