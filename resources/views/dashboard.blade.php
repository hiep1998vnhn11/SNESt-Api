@extends('layouts.app')
@section('title', 'Dashboard')
@section('navbar')
    @parent
@endsection

@section('content')
    <div style="min-height: 576px;">
        dashboard
        <a href="{{ route('logout') }}"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

        <livewire:user />
    </div>
@endsection
