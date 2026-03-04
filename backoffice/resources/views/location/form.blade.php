<div class="box box-info padding-1">
    <div class="box-body">

        <div class="form-group">
            {{ Form::label('name') }}
            {{ Form::text('name', $location->name, ['class' => 'form-control' . ($errors->has('name') ? ' is-invalid' : ''), 'placeholder' => 'Name']) }}
            {!! $errors->first('name', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('description') }}
            {{ Form::text('description', $location->description, ['class' => 'form-control' . ($errors->has('description') ? ' is-invalid' : ''), 'placeholder' => 'Description']) }}
            {!! $errors->first('description', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('hashcode') }}
            {{ Form::text('hashcode', $location->hashcode, ['class' => 'form-control' . ($errors->has('hashcode') ? ' is-invalid' : ''), 'placeholder' => 'Hashcode']) }}
            {!! $errors->first('hashcode', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('app_id') }}
            {{ Form::text('app_id', $app_id, ['class' => 'form-control' . ($errors->has('app_id') ? ' is-invalid' : ''), 'placeholder' => 'App Id']) }}
            {!! $errors->first('app_id', '<div class="invalid-feedback">:message</div>') !!}
        </div>

    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>
