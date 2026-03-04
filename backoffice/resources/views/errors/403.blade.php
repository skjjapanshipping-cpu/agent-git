@extends('errors::minimal')

@section('title', 'ไม่มีสิทธิ์เข้าถึง')
@section('code', '403')
@section('message', $exception->getMessage() ?: 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้')

<script>
    window.setTimeout(function(){
        window.location.href = "{{ url('home') }}";
    }, 2000);
</script>



