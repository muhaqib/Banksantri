@extends('layouts.app')

@section('header-title', 'Input Prestasi Hafalan Kitab')

@section('content')
    @include('pages.admin.prestasi.form', ['prestasi' => null])
@endsection
