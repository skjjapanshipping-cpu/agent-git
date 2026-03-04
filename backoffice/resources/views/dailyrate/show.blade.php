@extends('layouts.app')

@section('template_title')
    {{ $dailyrate->name ?? "{{ __('Show') Dailyrate" }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Dailyrate</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('dailyrates.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        
                        <div class="form-group">
                            <strong>Name:</strong>
                            {{ $dailyrate->name }}
                        </div>
                        <div class="form-group">
                            <strong>Rateprice:</strong>
                            {{ $dailyrate->rateprice }}
                        </div>
                        <div class="form-group">
                            <strong>Datetimerate:</strong>
                            {{ $dailyrate->datetimerate }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
