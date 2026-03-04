@extends('layouts.app')
@section('template_title')
    Import Shipping Data
@endsection

{{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />--}}

@section('content')
<div class="container">
    <div class="card bg-light mt-3">
        <div class="card-header">
            Import Shipping Data
        </div>
        <div class="card-body">
            <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label >เลือกไฟล์ Excel</label>
                <input type="file" name="file" class="form-control" />
                <br>
                <a class="btn btn-danger" href="{{route('tracks.index')}}">Back</a>
                <button class="btn btn-success">Import</button>
{{--                <a class="btn btn-warning" href="#">Export User Data</a>--}}

            </form>
        </div>
    </div>
</div>
@endsection
