@extends('layouts.app')

@section('template_title')
    {{ $location->name ?? "{{ __('Show') Location" }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Location</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('locations.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        
                        <div class="form-group">
                            <strong>Name:</strong>
                            {{ $location->name }}
                        </div>
                        <div class="form-group">
                            <strong>Description:</strong>
                            {{ $location->description }}
                        </div>
                        <div class="form-group">
                            <strong>Hashcode:</strong>
                            {{ $location->hashcode }}
                        </div>
                        <div class="form-group">
                            <strong>App Id:</strong>
                            {{ $location->app_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
