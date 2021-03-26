<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Snest Api</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/ef6ee49e4c.js" crossorigin="anonymous"></script>

    <style>


    </style>
    @livewireStyles
</head>

<body class="antialiased">

    @section('sidebar')
    @show
    <div class="container mx-auto">
        @yield('content')
    </div>

    @livewireScripts
</body>

</html>
