@extends('layouts.app')

@section('template_title')
    {{ $customershipping->name ?? "{{ __('Show') Customershipping" }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Customershipping</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('customershippings.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        
                        <div class="form-group">
                            <strong>Ship Date:</strong>
                            {{ $customershipping->ship_date }}
                        </div>
                        <div class="form-group">
                            <strong>Customerno:</strong>
                            {{ $customershipping->customerno }}
                        </div>
                        <div class="form-group">
                            <strong>Track No:</strong>
                            {{ $customershipping->track_no }}
                        </div>
                        <div class="form-group">
                            <strong>Cod:</strong>
                            {{ $customershipping->cod }}
                        </div>
                        <div class="form-group">
                            <strong>Weight:</strong>
                            {{ $customershipping->weight }}
                        </div>
                        <div class="form-group">
                            <strong>Unit Price:</strong>
                            {{ $customershipping->unit_price }}
                        </div>
                        <div class="form-group">
                            <strong>Import Cost:</strong>
                            {{ $customershipping->import_cost }}
                        </div>
                        <div class="form-group">
                            <strong>Box Image:</strong>
                            {{ $customershipping->box_image }}
                        </div>
                        <div class="form-group">
                            <strong>Product Image:</strong>
                            {{ $customershipping->product_image }}
                        </div>
                        <div class="form-group">
                            <strong>Box No:</strong>
                            {{ $customershipping->box_no }}
                        </div>
                        <div class="form-group">
                            <strong>Warehouse:</strong>
                            {{ $customershipping->warehouse }}
                        </div>
                        <div class="form-group">
                            <strong>Status:</strong>
                            {{ $customershipping->status }}
                        </div>
                        <div class="form-group">
                            <strong>Delivery Address:</strong>
                            {{ $customershipping->delivery_address }}
                        </div>
                        <div class="form-group">
                            <strong>Note:</strong>
                            {{ $customershipping->note }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
