@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ trans('HealthPay Payment Gateway Settings') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">{{ trans('admin/main.dashboard') }}</a></div>
            <div class="breadcrumb-item">{{ trans('HealthPay Settings') }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ trans('Gateway Configuration') }}</h4>
                        <div class="card-header-action">
                            <button type="button" class="btn btn-primary" id="testConnection">
                                <i class="fas fa-plug"></i> {{ trans('Test Connection') }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <form action="{{ route('admin.healthpay.update') }}" method="post">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('Status') }}</label>
                                        <select name="enabled" class="form-control @error('enabled') is-invalid @enderror">
                                            <option value="1" {{ ($settings['enabled'] ?? false) ? 'selected' : '' }}>
                                                {{ trans('Enabled') }}
                                            </option>
                                            <option value="0" {{ !($settings['enabled'] ?? false) ? 'selected' : '' }}>
                                                {{ trans('Disabled') }}
                                            </option>
                                        </select>
                                        @error('enabled')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">{{ trans('Enable or disable HealthPay payment gateway') }}</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('Mode') }}</label>
                                        <select name="mode" class="form-control @error('mode') is-invalid @enderror">
                                            <option value="sandbox" {{ ($settings['mode'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>
                                                {{ trans('Sandbox (Testing)') }}
                                            </option>
                                            <option value="live" {{ ($settings['mode'] ?? 'sandbox') == 'live' ? 'selected' : '' }}>
                                                {{ trans('Live (Production)') }}
                                            </option>
                                        </select>
                                        @error('mode')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">{{ trans('Select operating mode') }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('API Endpoint') }}</label>
                                        <select name="api_endpoint" class="form-control @error('api_endpoint') is-invalid @enderror">
                                            <option value="sandbox" {{ ($settings['api_endpoint'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>
                                                {{ trans('Sandbox') }} (https://api.beta.healthpay.tech/graphql)
                                            </option>
                                            <option value="production" {{ ($settings['api_endpoint'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>
                                                {{ trans('Production') }} (https://api.healthpay.tech/graphql)
                                            </option>
                                        </select>
                                        @error('api_endpoint')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">{{ trans('Select API endpoint') }}</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('Supported Currency') }}</label>
                                        <input type="text" class="form-control" value="EGP (Egyptian Pound)" disabled>
                                        <small class="form-text text-muted">{{ trans('HealthPay supports EGP only') }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('API Key') }} <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               name="api_key" 
                                               class="form-control @error('api_key') is-invalid @enderror" 
                                               value="{{ $settings['api_key'] ?? '' }}" 
                                               required
                                               placeholder="Enter your HealthPay API Key">
                                        @error('api_key')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">{{ trans('Your HealthPay API Key from portal') }}</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('API Secret') }} <span class="text-danger">*</span></label>
                                        <input type="password" 
                                               name="api_secret" 
                                               class="form-control @error('api_secret') is-invalid @enderror" 
                                               value="{{ $settings['api_secret'] ?? '' }}" 
                                               required
                                               placeholder="Enter your HealthPay API Secret">
                                        @error('api_secret')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">{{ trans('Your HealthPay API Secret from portal') }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('Webhook Secret') }}</label>
                                        <input type="password" 
                                               name="webhook_secret" 
                                               class="form-control @error('webhook_secret') is-invalid @enderror" 
                                               value="{{ $settings['webhook_secret'] ?? '' }}"
                                               placeholder="Enter webhook secret for signature verification">
                                        @error('webhook_secret')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">{{ trans('Optional: Secret key for webhook signature verification') }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> {{ trans('Important Information') }}</h6>
                                <ul class="mb-0">
                                    <li>{{ trans('Get your API credentials from HealthPay portal') }}</li>
                                    <li>{{ trans('Sandbox Portal') }}: <a href="https://portal.beta.healthpay.tech" target="_blank">https://portal.beta.healthpay.tech</a></li>
                                    <li>{{ trans('Production Portal') }}: <a href="https://portal.healthpay.tech" target="_blank">https://portal.healthpay.tech</a></li>
                                    <li>{{ trans('Test credentials are available in the documentation') }}</li>
                                </ul>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ trans('Save Settings') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Transaction Statistics Card -->
                <div class="card">
                    <div class="card-header">
                        <h4>{{ trans('Transaction Statistics') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-primary">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>{{ trans('Successful') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $stats['successful'] ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-warning">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>{{ trans('Pending') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $stats['pending'] ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-danger">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>{{ trans('Failed') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $stats['failed'] ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-statistic-1">
                                    <div class="card-icon bg-success">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>{{ trans('Total Amount') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            {{ $stats['total_amount'] ?? 0 }} EGP
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#testConnection').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true);
            btn.html('<i class="fas fa-spinner fa-spin"></i> Testing...');
            
            $.ajax({
                url: '{{ route("admin.healthpay.test") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: response.message,
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    var message = xhr.responseJSON?.message || 'Connection test failed';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: message,
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    btn.prop('disabled', false);
                    btn.html('<i class="fas fa-plug"></i> Test Connection');
                }
            });
        });
    });
</script>
@endpush
@endsection

