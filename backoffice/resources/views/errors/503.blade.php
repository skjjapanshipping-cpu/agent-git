@extends('errors::minimal')

@section('title', 'ระบบปิดปรับปรุง')
@section('code', '503')
@section('message', $exception->getMessage() ?: 'ระบบกำลังปิดปรับปรุงชั่วคราว กรุณากลับมาใหม่ภายหลัง')
