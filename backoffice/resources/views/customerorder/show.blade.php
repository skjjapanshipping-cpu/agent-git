@extends('layouts.app')

@section('template_title')
    {{ $customerorder->name ?? "{{ __('Show') Customerorder" }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Customerorder</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('customerorders.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        
                        <div class="form-group">
                            <strong>Order Date:</strong>
                            {{ $customerorder->order_date }}
                        </div>
                        <div class="form-group">
                            <strong>Customerno:</strong>
                            {{ $customerorder->customerno }}
                        </div>
                        <div class="form-group d-none">
                            <strong>Category:</strong>
                            {{ $customerorder->category }}
                        </div>
                        <div class="form-group">
                            <strong>Image Link:</strong>
                            {{ $customerorder->image_link }}
                        </div>
                        <div class="form-group">
                            <strong>Quantity:</strong>
                            {{ $customerorder->quantity }}
                        </div>
                        <div class="form-group">
                            <strong>Product Cost Yen:</strong>
                            {{ $customerorder->product_cost_yen }}
                        </div>
                        <div class="form-group">
                            <strong>Rate:</strong>
                            {{ $customerorder->rate }}
                        </div>
                        <div class="form-group">
                            <strong>Product Cost Baht:</strong>
                            {{ $customerorder->product_cost_baht }}
                        </div>
                        <div class="form-group">
                            <strong>Status:</strong>
                            {{ $customerorder->status }}
                        </div>
                        <div class="form-group">
                            <strong>Tracking Number:</strong>
                            {{ $customerorder->tracking_number }}
                        </div>
                        <div class="form-group">
                            <strong>Cutoff Date:</strong>
                            {{ $customerorder->cutoff_date }}
                        </div>
                        <div class="form-group">
                            <strong>Shipping Status:</strong>
                            {{ $customerorder->shipping_status }}
                        </div>
                        <div class="form-group">
                            <strong>Note:</strong>
                            {{ $customerorder->note }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
