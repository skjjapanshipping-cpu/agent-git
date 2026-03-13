@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Customershipping
@endsection

@section('extra-css')
    @include('partials.form-modern-css')
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="">
            <div class="col-md-12">

                @includeif('partials.errors')

                <div class="card modern-form-card">
                    <div class="card-header modern-form-header">
                        <span class="modern-form-title"><i class="fa fa-edit"></i> แก้ไขรายการขนส่ง</span>
                        <a href="{{ route('customershippings.index') }}" class="btn-back"><i class="fa fa-arrow-left"></i> กลับ</a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('customershippings.update', $customershipping->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('customershipping.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
