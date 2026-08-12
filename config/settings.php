<?php

return [
    'logo' => env('APP_LOGO', 'default/logo.svg'),
    'sms' => env('SMS', 'off'),
    'mail' => env('MAIL', 'on'),
    'mail_from_address' => env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
    'mail_from_name' => env('MAIL_FROM_NAME', 'Example'),

    //on||off
    'reverb' => env('REVERB', 'off'),

    //yes||no
    'recaptcha' => env('RECAPTCHA_ENABLE', 'no'), 

    'pagination' => env('PAGINATION', '12'),

    'google-map' => env('GOOGLE_MAPS_API_KEY', 'AIzaSyDl7ias7CMBPanjqPisVXwhXXVth21Cl5Y'),

    'frontend' => env('FRONTEND_URL', 'https://vuqia.softvencealpha.com/auth'),
    'success_url' => env('SUCCESS_URL', 'https://vuqia.softvencealpha.com/user-dashboard/payment-successful'),
    'fail_url' => env('FAIL_URL', 'https://vuqia.softvencealpha.com/user-dashboard/payment-cancel'),
];
