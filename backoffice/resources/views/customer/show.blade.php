@extends('layouts.app')

@section('template_title')
    {{ $customer->name ?? "{{ __('Show') Customer" }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Customer</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('customers.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        
                        <div class="form-group">
                            <strong>Addr:</strong>
                            {{ $customer->addr }}
                        </div>
                        <div class="form-group">
                            <strong>Province:</strong>
                            {{ $customer->province }}
                        </div>
                        <div class="form-group">
                            <strong>Distrinct:</strong>
                            {{ $customer->distrinct }}
                        </div>
                        <div class="form-group">
                            <strong>Subdistrinct:</strong>
                            {{ $customer->subdistrinct }}
                        </div>
                        <div class="form-group">
                            <strong>Postcode:</strong>
                            {{ $customer->postcode }}
                        </div>
                        <div class="form-group">
                            <strong>Name:</strong>
                            {{ $customer->name }}
                        </div>
                        <div class="form-group">
                            <strong>Email:</strong>
                            {{ $customer->email }}
                        </div>
                        <div class="form-group">
                            <strong>Mobile:</strong>
                            {{ $customer->mobile }}
                        </div>
                        <div class="form-group">
                            <strong>Avatar:</strong>
                            {{ $customer->avatar }}
                        </div>
                        <div class="form-group">
                            <strong>Customerno:</strong>
                            {{ $customer->customerno }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
