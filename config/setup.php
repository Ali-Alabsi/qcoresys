<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auto setup on first HTTP request
    |--------------------------------------------------------------------------
    |
    | When enabled, the first visit migrates the database, seeds prototype
    | data, and creates the admin account. Safe to leave on: it no-ops after
    | the application is already initialized.
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
