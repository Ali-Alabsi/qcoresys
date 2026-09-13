<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auto setup on first HTTP request
    |--------------------------------------------------------------------------
    |
    | When enabled, the first HTTP visit runs migrate:fresh --seed once, then
    | writes storage/framework/setup_completed.lock. Later requests skip setup
    | so existing data is never wiped again.
    |
    */

    'auto' => filter_var(env('AUTO_SETUP', true), FILTER_VALIDATE_BOOLEAN),

    'admin' => [
        'name' => env('ADMIN_NAME', 'QCoreSys Administrator'),
        'email' => env('ADMIN_EMAIL', 'admin@qcoresys.com'),
        'username' => env('ADMIN_USERNAME', 'qcoresys.admin'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],

    'portal' => [
        'name' => env('PORTAL_DEMO_NAME', 'Portal Demo Customer'),
        'email' => env('PORTAL_DEMO_EMAIL', 'portal@qcoresys.com'),
        'username' => env('PORTAL_DEMO_USERNAME', 'portal.demo'),
        'password' => env('PORTAL_DEMO_PASSWORD', 'password'),
        'company' => env('PORTAL_DEMO_COMPANY', 'Demo Client Co.'),
    ],

];
