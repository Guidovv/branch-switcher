<?php

return [
	'assets' => [
		'css' => __DIR__ . '/../resources/sass/branch-switcher.scss',
		'js' => __DIR__ . '/../resources/js/branch-switcher.js',
	],

    'enabled' => true,

    /*
     |--------------------------------------------------------------------------
     | BranchSwitcer environments
     |--------------------------------------------------------------------------
     |
     | Even when 'enabled' is set to true, the branch switcher will only work
     | if you are on one of the environments specified down below
     |
     */
    'environments' => ['local', 'testing'],

    /*
     |--------------------------------------------------------------------------
     | BranchSwitcer route prefix
     |--------------------------------------------------------------------------
     |
     | Sometimes you want to set route prefix to be used by the branch switcher to load
     | its resources from. Usually the need comes from misconfigured web server or
     | from trying to overcome bugs like this: http://trac.nginx.org/nginx/ticket/97
     |
     */
    'route_prefix' => '_branch-switcher',

    /*
     |--------------------------------------------------------------------------
     | BranchSwitcer route domain
     |--------------------------------------------------------------------------
     |
     | By default the branch switcher route served from the same domain that request served.
     | To override default domain, specify it as a non-empty value.
     */
    'route_domain' => null,

    'commands' => [
        'npm install' => [
            'default' => 1,
        ],
        'composer install' => [
            'default' => 1,
        ],
        'php artisan migrate' => [
            'default' => 1,
        ],
        'php artisan migrate:fresh' => [
            'default' => 1,
        ],
        'php artisan db:seed' => [
            'default' => 0,
        ],
    ],
];
