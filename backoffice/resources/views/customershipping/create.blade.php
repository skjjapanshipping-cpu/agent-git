@extends('layouts.app')

@section('template_title')
    {{ __('Create') }} Customershipping
@endsection

@section('extra-css')
    @include('partials.form-modern-css')
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">

                @includeif('partials.errors')

                <div class="card modern-form-card">
                    <div class="card-header modern-form-header">
                        <span class="modern-form-title"><i class="fa fa-plus-circle"></i> สร้างรายการขนส่งใหม่</span>
                        <a href="{{ route('customershippings.index') }}" class="btn-back"><i class="fa fa-arrow-left"></i> กลับ</a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('customershippings.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            @include('customershipping.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
