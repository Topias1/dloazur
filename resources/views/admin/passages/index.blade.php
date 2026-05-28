@extends('layouts.admin')

@section('title', 'Passages · Dlo Azur')

@section('sidebar')
    <x-admin.sidebar :user="auth()->user()" />
@endsection

@section('topbar')
    <x-admin.topbar />
@endsection

@section('main')
    <livewire:passage-index />
    <x-admin.mobile-bottom-nav />
@endsection
