<?php

namespace BranchSwitcher\Middleware;

use Closure;
use Illuminate\Http\Request;
use BranchSwitcher\BranchSwitcher;
use Illuminate\Contracts\Container\Container;

class InjectBranchSwitcher
{
    /**
     * The App container
     *
     * @var Container
     */
    protected $container;

    /**
     * The BranchSwitcher instance
     *
     * @var branchSwitcher
     */
    protected $branchSwitcher;

    /**
     * Create a new middleware instance.
     *
     * @param  Container $container
     * @param  BranchSwitcher $branchSwitcher
     */
    public function __construct(Container $container, BranchSwitcher $branchSwitcher)
    {
        $this->container = $container;
        $this->branchSwitcher = $branchSwitcher;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $this->branchSwitcher->isEnabled()) {
            return $next($request);
        }

        $this->branchSwitcher->boot();

        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        // Modify the response to add the BranchSwitcher
        $this->branchSwitcher->modifyResponse($request, $response);

        return $response;
    }
}
