<div class="box box-info padding-1">
    <div class="box-body">

        <div class="form-group">
{{--            {{ Form::label('name') }}--}}
            {{ Form::hidden('name', isset($dailyrate->name) ? $dailyrate->name : 'YEN', ['class' => 'form-control' . ($errors->has('name') ? ' is-invalid' : ''), 'placeholder' => 'Name']) }}
{{--            {!! $errors->first('name', '<div class="invalid-feedback">:message</div>') !!}--}}
        </div>
        <div class="form-group">
            {{ Form::label('Order Rate (1 เยน/บาท)') }}
            {{ Form::number('rateprice', $dailyrate->rateprice, ['class' => 'form-control' . ($errors->has('rateprice') ? ' is-invalid' : ''), 'placeholder' => 'Rateprice', 'step' => '0.001']) }}
            {!! $errors->first('rateprice', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('COD Rate (1 เยน/บาท)') }}
            {{ Form::number('cod_rate', $dailyrate->cod_rate ?? 0.25, ['class' => 'form-control' . ($errors->has('cod_rate') ? ' is-invalid' : ''), 'placeholder' => 'COD Rate', 'step' => '0.001']) }}
            {!! $errors->first('cod_rate', '<div class="invalid-feedback">:message</div>') !!}
            <small class="form-text text-muted">ใช้คำนวณยอด COD ในหน้า MyShipping (ค่าเริ่มต้น 0.25)</small>
        </div>
        <div class="form-group">
            {{ Form::label('Date') }}
            {{ Form::datetimeLocal('datetimerate', isset($dailyrate->datetimerate) ? $dailyrate->datetimerate : \Carbon\Carbon::now(), ['class' => 'form-control' . ($errors->has('datetimerate') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            {!! $errors->first('datetimerate', '<div class="invalid-feedback">:message</div>') !!}
        </div>


    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>
