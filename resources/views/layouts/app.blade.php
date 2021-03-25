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
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    min-height: 100%;
}

.welcome-container {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    max-width: 1200px;
    margin: 40px 0;
}

.welcome-container .welcome-card {
    position: relative;
    min-width: 320px;
    height: 440px;
    box-shadow: inset 5px 5px 5px rgba(0, 0, 0, 0.2),
        inset -5px -5px 15px rgba(255, 255, 255, 0.1),
        5px 5px 15px rgba(0, 0, 0, 0.3), -5px -5px 15px rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    margin: 30px;
}

.welcome-container .welcome-card .welcome-box {
    position: absolute;
    top: 20px;
    left: 20px;
    right: 20px;
    bottom: 20px;
    background: #2a2b2f;
    border: 2px solid #1e1f23;
    border-radius: 15px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    transition: 0.5%;
    display: flex;
    justify-content: center;
    align-items: center;
    -webkit-transition: transform 0.5s ease-in-out;
}

.welcome-container .welcome-card .welcome-box::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 50%;
    height: 100%;
    background: rgba(255, 255, 255, 0.05);
    pointer-events: none;
}
.welcome-container .welcome-card .welcome-box:hover {
    -webkit-transition: transform 0.5s ease-in-out;
    -moz-transition: transform 1s ease-in-out;
    -ms-transition: transform 1s ease-in-out;
    -webkit-transform: scale(1.5);

    transform: translateY(-50px);
    box-shadow: 0 40px 70px rgba(0, 0, 0, 0.5);
}

.welcome-container .welcome-card .welcome-box .welcome-content {
    padding: 20px;
    text-align: center;
}

.welcome-container .welcome-card .welcome-box .welcome-content h2 {
    position: absolute;
    top: -10px;
    right: 30px;
    font-size: 5em;
    color: rgba(255, 255, 255, 0.05);
}

.welcome-container .welcome-card .welcome-box .welcome-content h3 {
    font-size: 1.8rem;
    z-index: 1000;
    color: rgba(255, 255, 255, 0.5);
    transition: 0.5%;
}

.welcome-container .welcome-card .welcome-box .welcome-content p {
    font-size: 0.8rem;
    font-weight: 300;
    z-index: 1000;
    color: rgba(255, 255, 255, 0.5);
    transition: 0.5%;
}

.welcome-container .welcome-card .welcome-box .welcome-content a {
    position: relative;
    display: inline-block;
    padding: 8px 20px;
    background: #000;
    margin-top: 15px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 400;
    z-index: 1000;
    color: #fff;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.welcome-container .welcome-card:nth-child(1) .welcome-box .welcome-content a {
    background: #2196f3;
}

.welcome-container .welcome-card:nth-child(2) .welcome-box .welcome-content a {
    background: #abd122;
}

.container .card:nth-child(3) .box .content a {
    background: #123543;
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