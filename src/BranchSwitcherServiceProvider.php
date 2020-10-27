<?php

namespace BranchSwitcher;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use BranchSwitcher\Middleware\InjectBranchSwitcher;

class BranchSwitcherServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerMiddleware(InjectBranchSwitcher::class);

		$this->loadViewsFrom(realpath(__DIR__ . '/../resources/views'), 'branch-switcher');
        $this->loadRoutesFrom(realpath(__DIR__ . '/branch-switcher-routes.php'));

    	$this->publishes([
			__DIR__ . '/../resources/sass' => public_path('vendor/branch-switcher'),
			__DIR__ . '/../resources/js' => public_path('vendor/branch-switcher'),
		], 'branch-switcher-resources');

        $this->publishes([
            __DIR__ . '/../config/branch-switcher.php' => config_path('branch-switcher.php'),
        ], 'branch-switcher-config');
    }

    public function register()
    {
        $configPath = __DIR__ . '/../config/branch-switcher.php';
        $this->mergeConfigFrom($configPath, 'branch-switcher');
    }

    /**
     * Register the BranchSwitcher Middleware
     *
     * @param  string $middleware
     */
    protected function registerMiddleware($middleware)
    {
        $kernel = $this->app[Kernel::class];
        $kernel->pushMiddleware($middleware);
    }

    protected function getRouter() {
        return $this->app['router'];
    }
}
