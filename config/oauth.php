<?php
return [
  'facebook' => [
    'app_id' => env('OAUTH_FACEBOOK_APP_ID', null),
    'app_secret' => env('OAUTH_FACEBOOK_APP_SECRET', null),
    'type' => 'facebook'
  ],
  'google' => [
    'client_id' => env('OAUTH_GOOGLE_CLIENT_ID', null),
    'type' => 'google'
  ]
];
