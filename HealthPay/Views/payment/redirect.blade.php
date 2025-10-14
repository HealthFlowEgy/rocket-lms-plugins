@extends('web.default.layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                            <span class="sr-only">{{ trans('Loading...') }}</span>
                        </div>
                    </div>
                    
                    <h3 class="mb-3">{{ trans('Redirecting to HealthPay') }}</h3>
                    <p class="text-muted mb-4">{{ trans('Please wait while we redirect you to complete your payment securely') }}</p>
                    
                    <div class="alert alert-info mb-4">
                        <h5 class="alert-heading">{{ trans('Order Details') }}</h5>
                        <hr>
                        <p class="mb-1"><strong>{{ trans('Order ID') }}:</strong> #{{ $order->id ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>{{ trans('Amount') }}:</strong> {{ $order->amount ?? 0 }} EGP</p>
                        <p class="mb-0"><strong>{{ trans('Payment Method') }}:</strong> HealthPay</p>
                    </div>
                    
                    <div class="mt-4">
                        <p class="text-muted small">
                            <i class="fas fa-lock"></i> {{ trans('Secure payment powered by HealthPay') }}
                        </p>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ $paymentUrl }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-right"></i> {{ trans('Continue to Payment') }}
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="{{ route('panel') }}" class="text-muted">
                    <i class="fas fa-arrow-left"></i> {{ trans('Cancel and return to dashboard') }}
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-redirect after displaying message for 3 seconds
    setTimeout(function() {
        window.location.href = "{{ $paymentUrl }}";
    }, 3000);
</script>

<style>
    .spinner-border {
        border-width: 0.3rem;
    }
    
    .card {
        border-radius: 15px;
    }
    
    .alert-info {
        background-color: #e7f3ff;
        border-color: #b3d9ff;
        color: #004085;
    }
</style>
@endsection

