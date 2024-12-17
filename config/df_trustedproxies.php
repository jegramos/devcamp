<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | List of trusted proxies in the network, these IPs should be added
    | in CIDR notation or exact IP addresses.  Requests coming from
    | these proxies will be trusted to provide the correct client IP address.
    |
    */
    'proxies' => env('TRUSTED_PROXIES') ? explode(',', env('TRUSTED_PROXIES')) : [],
];
