@extends('layouts.app')

@section('header-title', 'Edit Prestasi Hafalan Kitab')

@section('content')
    @include('pages.admin.prestasi.form', ['prestasi' => $prestasi])
@endsection
