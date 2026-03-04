@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Track
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="">
            <div class="col-md-12">

                @includeif('partials.errors')

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Update') }} Track</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tracks.update', $track->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('track.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
