<?php
    $routeConfig = [
        'prefix' => app('config')->get('branch-switcher.route_prefix'),
        'domain' => app('config')->get('branch-switcher.route_domain'),
        'middleware' => [\BranchSwitcher\Middleware\BranchSwitcherEnabled::class],
    ];

    app('router')->group($routeConfig, function ($router) {
        $router->get('assets/stylesheets', [
            'uses' => 'BranchSwitcher\Controllers\AssetController@css',
            'as' => 'branch-switcher.assets.css',
        ]);

        $router->get('assets/javascript', [
            'uses' => 'BranchSwitcher\Controllers\AssetController@js',
            'as' => 'branch-switcher.assets.js',
        ]);

        $router->post('switch', 'BranchSwitcher\Controllers\ApiController@switch');
    });
