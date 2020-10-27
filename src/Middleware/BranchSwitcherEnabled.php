<?php

namespace BranchSwitcher\Middleware;

use Closure;
use Illuminate\Http\Request;
use BranchSwitcher\BranchSwitcher;

class BranchSwitcherEnabled
{
    /**
     * The BranchSwitcher instance
     *
     * @var BranchSwitcher
     */
    protected $branchSwitcher;

    /**
     * Create a new middleware instance.
     *
     * @param  BranchSwitcher $branchSwitcher
     */
    public function __construct(BranchSwitcher $branchSwitcher)
    {
        $this->branchSwitcher = $branchSwitcher;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $this->branchSwitcher->isEnabled()) {
            abort(404);
        }

        return $next($request);

    }
}
