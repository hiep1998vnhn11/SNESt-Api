@extends('layouts.app')
@section('sidebar')
    @parent
@endsection

@section('content')
    <div class="welcome-container">
        <div class="welcome-card">
            <div class="welcome-box">
                <div class="welcome-content">
                    <p>
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                        when an unknown printer took a galley of type and scrambled it to make a type
                        specimen book.
                    </p>
                    <a href="{{ env('SNEST_URL') }}" target="_blank">
                        {{ __('common.go_to') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="welcome-card">
            <div class="welcome-box">
                <div class="welcome-content">
                    <p>
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                        when an unknown printer took a galley of type and scrambled it to make a type
                        specimen book.
                    </p>
                    <a href="{{ route('login') }}">
                        {{ __('common.login') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
