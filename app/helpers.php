<?php

use App\Support\Branding;

if (!function_exists('branding')) {
    function branding(): Branding
    {
        return app(Branding::class);
    }
}
