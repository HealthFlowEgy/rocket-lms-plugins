@extends('web.default.layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="error-icon">
                            <i class="fas fa-times-circle text-danger" style="font-size: 80px;"></i>
                        </div>
                    </div>
                    
                    <h2 class="text-danger mb-3">{{ trans('Payment Failed') }}</h2>
                    <p class="text-muted mb-4">{{ trans('Unfortunately, your payment could not be processed') }}</p>
                    
                    @if(session('error'))
                        <div class="alert alert-danger mb-4">
                            <h5 class="alert-heading">{{ trans('Error Details') }}</h5>
                            <hr>
                            <p class="mb-0">{{ session('error') }}</p>
                        </div>
                    @else
                        <div class="alert alert-warning mb-4">
                            <h5 class="alert-heading">{{ trans('What happened?') }}</h5>
                            <hr>
                            <p class="mb-0">{{ trans('The payment was cancelled or failed to process. No charges have been made to your account.') }}</p>
                        </div>
                    @endif
                    
                    <div class="mb-4">
                        <h5>{{ trans('Common reasons for payment failure:') }}</h5>
                        <ul class="text-left d-inline-block">
                            <li>{{ trans('Insufficient funds in your wallet') }}</li>
                            <li>{{ trans('Payment was cancelled by user') }}</li>
                            <li>{{ trans('Network or connection issues') }}</li>
                            <li>{{ trans('Invalid payment credentials') }}</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('cart') }}" class="btn btn-primary btn-lg mr-2">
                            <i class="fas fa-redo"></i> {{ trans('Try Again') }}
                        </a>
                        <a href="{{ route('panel') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-home"></i> {{ trans('Go to Dashboard') }}
                        </a>
                    </div>
                    
                    <div class="mt-4">
                        <p class="text-muted small">
                            {{ trans('Need help?') }} 
                            <a href="{{ route('contact') }}">{{ trans('Contact Support') }}</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .error-icon {
        animation: shake 0.5s;
    }

    @keyframes shake {
        0%, 100% {
            transform: translateX(0);
        }
        10%, 30%, 50%, 70%, 90% {
            transform: translateX(-10px);
        }
        20%, 40%, 60%, 80% {
            transform: translateX(10px);
        }
    }

    .card {
        border-radius: 15px;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffeaa7;
        color: #856404;
    }

    ul {
        text-align: left;
    }
</style>
@endsection

