@extends('errors::minimal')

@section('title', 'เซสชันหมดอายุ')
@section('code', '419')
@section('message', 'เซสชันหมดอายุแล้ว กำลังพากลับหน้าหลัก...')
<script>
window.setTimeout(function(){
    window.location.href = "{{ url('home') }}";
}, 300);
</script>
