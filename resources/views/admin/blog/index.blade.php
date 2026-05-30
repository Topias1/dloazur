@extends('layouts.admin')

@section('title', 'Blog · Dlo Azur')

@section('sidebar')
    <x-admin.sidebar :user="auth()->user()" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
    <livewire:post-index />
    <x-admin.mobile-bottom-nav />
@endsection
