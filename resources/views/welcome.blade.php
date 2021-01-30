@extends('layouts.app')
@section('sidebar')
@parent
@endsection

@section('content')
<div class="h-screen overflow-hidden flex items-center justify-center">
    <!-- light mode -->
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden shadow-md">
            <!-- card header -->
            <div class="px-6 py-4 bg-white border-b border-gray-200 font-bold uppercase">
                {{__('common.snest')}}
            </div>

            <!-- card body -->
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- content goes here -->
                Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                when an unknown printer took a galley of type and scrambled it to make a type
                specimen book.
            </div>

            <!-- card footer -->
            <div class="p-6 bg-white border-gray-200 text-right">
                <!-- button link -->
                <a class="bg-blue-500 shadow-md text-sm text-white font-bold py-3 md:px-8 px-4 hover:bg-blue-400 rounded uppercase"
                    href="{{env('SNEST_URL')}}" target="_blank" rel="noopener noreferrer">
                    {{__('common.go_to')}}
                </a>
            </div>
        </div>
    </div>
    <!-- divider -->
    <hr class="my-6">
    <!-- dark mode -->
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="overflow-hidden shadow-md text-gray-100">
            <!-- card header -->
            <div class="px-6 py-4 bg-gray-800 border-b border-gray-600 font-bold uppercase">
                {{__('common.snestApi')}}
            </div>

            <!-- card body -->
            <div class="p-6 bg-gray-800 border-b border-gray-600">
                <!-- content goes here -->
                Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                when an unknown printer took a galley of type and scrambled it to make a type
                specimen book.
            </div>

            <!-- card footer -->
            <div class="p-6 bg-gray-800 border-gray-200 text-right">
                <!-- button link -->
                <a class="bg-blue-500 shadow-md text-sm text-white font-bold py-3 md:px-8 px-4 hover:bg-blue-400 rounded uppercase"
                    href="/admin/login">
                    {{__('common.login')}}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection