<?php

namespace BranchSwitcher\Controllers;

use BranchSwitcher\BranchSwitcher;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

if (class_exists('Illuminate\Routing\Controller')) {

    class BaseController extends Controller
    {
        protected $branchSwitcher;

        public function __construct(Request $request, BranchSwitcher $branchSwitcher)
        {
            $this->branchSwitcher = $branchSwitcher;

            if ($request->hasSession()) {
                $request->session()->reflash();
            }

            $this->middleware(function ($request, $next) {
                return $next($request);
            });
        }
    }

} else {

    class BaseController
    {
        protected $branchSwitcher;

        public function __construct(Request $request, BranchSwitcher $branchSwitcher)
        {
            $this->branchSwitcher = $branchSwitcher;

            if ($request->hasSession()) {
                $request->session()->reflash();
            }
        }
    }
}
