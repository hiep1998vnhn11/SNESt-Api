<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Snest Api - @yield('title')</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Styles -->
  <link href="https://unpkg.com/tailwindcss@2.0.2/dist/tailwind.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Nunito';
    }
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