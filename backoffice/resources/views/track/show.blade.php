@extends('layouts.app')

@section('template_title')
    {{ $track->name ?? "{{ __('Show') Track" }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Track</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('tracks.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="form-group">
                            <strong>Customer Name:</strong>
                            {{ $track->customer_name }}
                        </div>
                        <div class="form-group">
                            <strong>Track No:</strong>
                            {{ $track->track_no }}
                        </div>
                        <div class="form-group">
                            <strong>Cod:</strong>
                            {{ $track->cod }}
                        </div>
                        <div class="form-group">
                            <strong>Weight:</strong>
                            {{ $track->weight }}
                        </div>
                        <div class="form-group">
                            <strong>Source Date:</strong>
                            {{ $track->source_date }}
                        </div>
                        <div class="form-group">
                            <strong>Ship Date:</strong>
                            {{ $track->ship_date }}
                        </div>
                        <div class="form-group">
                            <strong>Destination Date:</strong>
                            {{ $track->destination_date }}
                        </div>
                        <div class="form-group d-none">
                            <strong>Note:</strong>
                            {{ $track->note }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
