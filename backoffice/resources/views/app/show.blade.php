@extends('layouts.app')

@section('template_title')
    {{ $app->name ?? "{{ __('Show') App" }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} App</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('apps.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        
                        <div class="form-group">
                            <strong>Name:</strong>
                            {{ $app->name }}
                        </div>
                        <div class="form-group">
                            <strong>Description:</strong>
                            {{ $app->description }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
