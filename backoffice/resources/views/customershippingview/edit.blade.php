@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Customershipping Address
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-12">

                @includeif('partials.errors')

                <div class="shipping-edit-card">
                    <div class="shipping-edit-header">
                        <div class="shipping-edit-header-icon">
                            <i class="fa fa-truck"></i>
                        </div>
                        <div>
                            <h2>ข้อมูลที่อยู่จัดส่งสินค้า</h2>
                            <p>เลขพัสดุ: <strong>{{ $customershipping->track_no }}</strong></p>
                        </div>
                    </div>
                    <div class="shipping-edit-body">
                        <form method="POST" action="{{ route('customershippingview.update', $customershipping->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('customershippingview.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
