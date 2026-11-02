<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Branding defaults
    |--------------------------------------------------------------------------
    | Used until an admin saves their own values from Settings (admin/settings).
    */
    'brand' => [
        'name' => env('HELPDESK_NAME', 'نظام الدعم الفني'),
        'short_name' => env('HELPDESK_SHORT_NAME', 'الدعم الفني'),
        'organization' => env('HELPDESK_ORGANIZATION', ''),
        'primary_color' => env('HELPDESK_PRIMARY_COLOR', '#0077C2'),
    ],

    /*
    | Folder under public/ for the uploaded logo and the icons made from it.
    | On hosts with an ephemeral disk (Railway, containers) use "storage/branding":
    | it lives in storage/app/public (served through the storage link), which
    | can sit on a persistent volume together with ticket attachments.
    */
    'branding_directory' => env('HELPDESK_BRANDING_DIRECTORY', 'branding'),

    /*
    | First root admin for `php artisan helpdesk:install --no-interaction`
    | (deploy scripts). Used only while no root admin exists; remove the
    | password variable after the first deploy.
    */
    'admin' => [
        'username' => env('HELPDESK_ADMIN_USERNAME', 'admin'),
        'name' => env('HELPDESK_ADMIN_NAME', 'مدير النظام'),
        'password' => env('HELPDESK_ADMIN_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP / Active Directory sign-in (optional)
    |--------------------------------------------------------------------------
    | Off by default: everyone signs in with a local account. When enabled,
    | users without a local account are checked against AD and created on
    | their first successful sign-in (as Members).
    */
    'ldap' => [
        'enabled' => (bool) env('LDAP_ENABLED', false),
        'host' => env('LDAP_HOST', '127.0.0.1'),
        'port' => (int) env('LDAP_PORT', 389),
        // users sign in as username@domain (User Principal Name)
        'domain' => env('LDAP_DOMAIN', 'example.com'),
        'base_dn' => env('LDAP_BASE_DN', 'dc=example,dc=com'),
        // users whose DN contains this text keep the branches and office set by an admin
        // (for example "OU=IT,OU=HQ"); empty = everyone is synced from AD
        'protected_dn' => env('LDAP_PROTECTED_DN', ''),
    ],

];
