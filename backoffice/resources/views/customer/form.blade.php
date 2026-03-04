<div class="box box-info padding-1">
    <div class="box-body">
        <div class="form-group">
            <strong><p>{{ 'รหัสลูกค้า: '.strtoupper($customer->customerno) }}</p></strong>
{{--            {{ Form::text('customerno', $customer->customerno, ['class' => 'form-control' . ($errors->has('customerno') ? ' is-invalid' : ''), 'placeholder' => 'Customerno']) }}--}}
{{--            {!! $errors->first('customerno', '<div class="invalid-feedback">:message</div>') !!}--}}
        </div>

        <div class="form-group">
            {{ Form::label('cus_unit_price','ราคาต่อหน่วย') }}
            {{ Form::number('cus_unit_price', $customer->cus_unit_price, ['class' => 'form-control' . ($errors->has('cus_unit_price') ? ' is-invalid' : ''), 'step' => '0.01', 'placeholder' => '','id'=>'cus_unit_price']) }}
            {!! $errors->first('cus_unit_price', '<div class="invalid-feedback">:message</div>') !!}
        </div>

        <div class="form-group">
            <label class="form-lebel">ประเภทการจัดส่ง</label>
            <select class="form-control @error('delivery_type_id') is-invalid @enderror" id="delivery_type_id" name="delivery_type_id">
                <option value="">กรุณาเลือกประเภทการจัดส่ง</option>
                @if(isset($deliveryTypes))
                    @foreach($deliveryTypes as $deliveryType)
                        @if($deliveryType->id != 3)
                            <option value="{{ $deliveryType->id }}"
                                {{ ($customer->delivery_type_id ?? 1) == $deliveryType->id ? 'selected' : '' }}>
                                {{ $deliveryType->name }}</option>
                        @endif
                    @endforeach
                @endif
            </select>
            @error('delivery_type_id')
            <label id="delivery_type_id-error" class="error" for="delivery_type_id">กรุณาเลือกประเภทการจัดส่ง</label>
            @enderror
        </div>

        <div class="form-group">
            {{ Form::label('name') }}
            {{ Form::text('name', $customer->name, ['class' => 'form-control' . ($errors->has('name') ? ' is-invalid' : ''), 'placeholder' => 'Name']) }}
            {!! $errors->first('name', '<div class="invalid-feedback">:message</div>') !!}
        </div>

        <div class="form-group">
            {{ Form::label('mobile') }}
            {{ Form::text('mobile', $customer->mobile, ['class' => 'form-control' . ($errors->has('mobile') ? ' is-invalid' : ''), 'placeholder' => 'Mobile']) }}
            {!! $errors->first('mobile', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('addr') }}
            {{ Form::text('addr', $customer->addr, ['class' => 'form-control' . ($errors->has('addr') ? ' is-invalid' : ''), 'placeholder' => 'Addr']) }}
            {!! $errors->first('addr', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            <label class="form-lebel">จังหวัด</label>

            <select class="form-control @error('province') is-invalid @enderror" id="province" name="province" required >
                <option value="">กรุณาเลือกจังหวัด</option>
                @foreach($provinces as $item)
                    <option value="{{ $item->province }}"
                        {{ $customer->province == $item->province ? 'selected' : '' }}>{{ $item->province }}</option>
                @endforeach
            </select>
            @error('province')
            <label id="province-error" class="error" for="province">กรุณากรอก จังหวัด</label>
            @enderror
        </div>
        <div class="form-group">
            <label class="form-lebel">เขต/อำเภอ</label>

            <select class="form-control @error('distrinct') is-invalid @enderror"  id="distrinct" name="distrinct" required >
                <option value="">กรุณาเลือกเขต/อำเภอ</option>
                @foreach ($amphoes as $item)
                    <option value="{{ $item->amphoe }}"
                        {{ $customer->distrinct == $item->amphoe ? 'selected' : '' }}>
                        {{ $item->amphoe }}</option>
                @endforeach
            </select>
            @error('distrinct')
            <label id="distrinct-error" class="error" for="distrinct">กรุณากรอก เขต/อำเภอ</label>
            @enderror

        </div>
        <div class="form-group">
            <label class="form-lebel">แขวง/ตำบล</label>

            <select class="form-control @error('subdistrinct') is-invalid @enderror" id="subdistrinct" name="subdistrinct" >
                <option value="">กรุณาเลือกแขวง/ตำบล</option>
                @foreach($tambons as $item)
                    <option value="{{ $item->tambon }}"
                        {{ $customer->subdistrinct == $item->tambon ? 'selected' : '' }}>
                        {{ $item->tambon }}</option>
                @endforeach
            </select>

            @error('subdistrinct')
            <label id="subdistrinct-error" class="error" for="subdistrinct">กรุณากรอก แขวง/ตำบล</label>
            @enderror

        </div>
        <div class="form-group">
            <label class="form-lebel">รหัส ปณ.</label>

            <input id="postcode" name="postcode"  value="{{$customer->postcode}}" class="form-control @error('postcode') is-invalid @enderror" placeholder="รหัสไปรษณีย์" />

            @error('postcode')
            <label id="postcode-error" class="error" for="postcode">กรุณากรอก รหัสไปรษณีย์</label>
            @enderror

        </div>

{{--        <div class="form-group">--}}
{{--            {{ Form::label('email') }}--}}
{{--            {{ Form::text('email', $customer->email, ['class' => 'form-control' . ($errors->has('email') ? ' is-invalid' : ''), 'placeholder' => 'Email']) }}--}}
{{--            {!! $errors->first('email', '<div class="invalid-feedback">:message</div>') !!}--}}
{{--        </div>--}}

{{--        <div class="form-group">--}}
{{--            {{ Form::label('avatar') }}--}}
{{--            {{ Form::text('avatar', $customer->avatar, ['class' => 'form-control' . ($errors->has('avatar') ? ' is-invalid' : ''), 'placeholder' => 'Avatar']) }}--}}
{{--            {!! $errors->first('avatar', '<div class="invalid-feedback">:message</div>') !!}--}}
{{--        </div>--}}


    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>
@section('extra-script')
    <script>

        //EVENTS
        document.querySelector('#province').addEventListener('change', (event) => {
            showAmphoes();
        });
        document.querySelector('#distrinct').addEventListener('change', (event) => {
            showTambons();
        });
        document.querySelector('#subdistrinct').addEventListener('change', (event) => {
            showZipcode();
        });
    </script>
@endsection
