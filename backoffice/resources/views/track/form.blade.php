<div class="box box-info padding-1">
    <div class="box-body">

        <div class="form-group">
            {{ Form::label('customer_name') }}
            {{ Form::text('customer_name', $track->customer_name, ['class' => 'form-control' . ($errors->has('customer_name') ? ' is-invalid' : ''), 'placeholder' => 'Customer Name']) }}
            {!! $errors->first('customer_name', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('track_no') }}
            {{ Form::text('track_no', $track->track_no, ['class' => 'form-control' . ($errors->has('track_no') ? ' is-invalid' : ''), 'placeholder' => 'Track No']) }}
            {!! $errors->first('track_no', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('cod') }}
            {{ Form::text('cod', $track->cod, ['class' => 'form-control' . ($errors->has('cod') ? ' is-invalid' : ''), 'placeholder' => 'Cod']) }}
            {!! $errors->first('cod', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('weight') }}
            {{ Form::text('weight', $track->weight, ['class' => 'form-control' . ($errors->has('weight') ? ' is-invalid' : ''), 'placeholder' => 'Weight']) }}
            {!! $errors->first('weight', '<div class="invalid-feedback">:message</div>') !!}
        </div>
{{--        <div class="form-group">--}}
{{--            {{ Form::label('source_date') }}--}}
{{--            {{ Form::text('source_date', $track->source_date, ['class' => 'form-control' . ($errors->has('source_date') ? ' is-invalid' : ''), 'placeholder' => 'Source Date']) }}--}}
{{--            {!! $errors->first('source_date', '<div class="invalid-feedback">:message</div>') !!}--}}
{{--        </div>--}}

        <div class="form-group">
                {{ Form::label('source_date', 'วันที่สินค้าถึงคลังญี่ปุ่น') }}
                {{ Form::date('source_date', $track->source_date??\Carbon\Carbon::now(), ['class' => 'form-control' . ($errors->has('source_date') ? ' is-invalid' : ''), 'placeholder' => '']) }}
                <div class="invalid-feedback">กรุณากรอกข้อมูล</div>
        </div>

{{--        <div class="form-group">--}}
{{--            {{ Form::label('ship_date') }}--}}
{{--            {{ Form::text('ship_date', $track->ship_date, ['class' => 'form-control' . ($errors->has('ship_date') ? ' is-invalid' : ''), 'placeholder' => 'Ship Date']) }}--}}
{{--            {!! $errors->first('ship_date', '<div class="invalid-feedback">:message</div>') !!}--}}
{{--        </div>--}}
        <div class="form-group">
            {{ Form::label('ship_date', 'รอบเรือวันที่') }}
            {{ Form::date('ship_date', $track->ship_date??\Carbon\Carbon::now(), ['class' => 'form-control' . ($errors->has('ship_date') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            <div class="invalid-feedback">กรุณากรอกข้อมูล</div>
        </div>
{{--        <div class="form-group">--}}
{{--            {{ Form::label('destination_date') }}--}}
{{--            {{ Form::text('destination_date', $track->destination_date, ['class' => 'form-control' . ($errors->has('destination_date') ? ' is-invalid' : ''), 'placeholder' => 'Destination Date']) }}--}}
{{--            {!! $errors->first('destination_date', '<div class="invalid-feedback">:message</div>') !!}--}}
{{--        </div>--}}
        <div class="form-group">
            {{ Form::label('destination_date', 'วันที่ สินค้าเข้าไทย') }}
            {{ Form::date('destination_date', $track->destination_date??\Carbon\Carbon::now(), ['class' => 'form-control' . ($errors->has('destination_date') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            <div class="invalid-feedback">กรุณากรอกข้อมูล</div>
        </div>
        <div class="form-group d-none">
            {{ Form::label('note') }}
            {{ Form::text('note', $track->note, ['class' => 'form-control' . ($errors->has('note') ? ' is-invalid' : ''), 'placeholder' => 'Note']) }}
            {!! $errors->first('note', '<div class="invalid-feedback">:message</div>') !!}
        </div>

    </div>
    <div class="box-footer mt20">
        <a class="btn btn-danger" href="{{route('tracks.index')}}">Back</a>
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>
