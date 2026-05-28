@extends('layouts.admin')

@section('title', 'Clients · Dlo Azur')

@section('sidebar')
    <x-admin.sidebar :user="auth()->user()" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
    <livewire:client-index />
    <x-admin.mobile-bottom-nav />
@endsection
