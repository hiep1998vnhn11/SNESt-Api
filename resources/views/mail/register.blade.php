<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>{{$details['title'] ?? 'Snest'}}</title>

  <style>
    body,
    html {
      padding: 0;
    }

    .mail {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      height: 100%;
      position: absolute;
      left: 0;
      top: 0;
    }

    .mail--container {
      width: 80%;
      height: 100%;
      position: absolute;
    }

    .mail--header {
      height: 70px;
      display: flex;
      justify-content: space-between;
      padding: 10px;
    }

    .mail--date {
      font-style: italic;
    }

    .mail--image {
      width: 100%;
      height: 300px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .mail--image img {
      height: 100%;
    }

    .mail--text {
      text-align: center;
    }

    .text--header {
      font-size: 2rem;
      font-weight: 500;
    }

    .text--content {}

    .mail--footer {
      position: absolute;
      height: 70px;
      bottom: 0;
      width: 100%;
      display: flex;
      justify-content: space-between;
    }

    .icon {
      width: 50px;
      height: 50px;
      border: solid 1px rgba(0, 0, 0, 0.08);
      padding: 2px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      margin-left: 10px;
    }

    .icon img {
      width: 80%;
      height: 80%;
    }

    .footer--right {
      display: flex;
    }

    .link {
      text-decoration: none;
      color: blue;
    }
  </style>
</head>

<body>
  <div class="mail">
    <div class="mail--container">
      <div class="mail--header">
        <div class="logo">
          <a target="_blank" class="link" href="{{env('SNEST_URL')}}">
            <img src="{{url('/assets/logo.png')}}">
          </a>
        </div>
        <div class="mail--date">
          {{$details['datetime'] ?? date('l jS \of F Y h:i:s A')}}
        </div>
      </div>
      <div class="mail--content">
        <div class="mail--image">
          <img src="{{url('/assets/mail.jpeg')}}">
        </div>
        <div class="mail--text">
          <div class="text--header">
            {{$details['header'] ?? date('Header')}}
          </div>
          <div class="text--content">
            {{$details['content'] ?? date('Content')}}
          </div>
        </div>
      </div>
      <div class="mail--footer">
        <div class="footer--left">
          <div>
            <a target="_blank" href="{{env('SNEST_URL')}}" class="link">
              Snest
            </a>
          </div>
          <div>
            <a target="_blank" href="mailto:hiep1998vnhn11@gmail.com" class="link">
              support@snest.com
            </a>
          </div>
        </div>
        <div class="footer--right">
          <a target="_blank" class="icon" href="{{env('SNEST_URL')}}">
            <img src="{{url('/assets/logo.png')}}">
          </a>
          <a target="_blank" class="icon" href="https://facebook.com/hieptv98">
            <img src="{{url('/assets/facebook.png')}}">
          </a>
        </div>
      </div>
    </div>
  </div>
</body>

</html>