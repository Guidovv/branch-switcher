<?php

use Illuminate\Support\Str;

if (!function_exists('branchSwitcher')) {
    /**
     * Get the BranchSwitcher instance
     *
     * @return BranchSwitcher\BranchSwitcher
     */
    function branchSwitcher()
    {
        return app(BranchSwitcher\BranchSwitcher::class);
    }
}

if (!function_exists('getBranches')) {
	/**
     * Return an array with all the branches and the one currently active
     *
     * @return array
     */
    function getBranches()
    {
    	$return = [
    		'all' => [],
    		'current' => '',
            'error' => false,
    	];

        exec('git branch 2>&1', $output, $code);

        if ($code != 0) {
            $return['error'] = $output;
        } elseif (! $output) {
            $return['error'] = 'No existing branches were found!';
        }

        if ($return['error']) {
            return $return;
        }

    	$branches = array_filter($output);
    	$branches = array_map('trim', $branches);
    	$return['all'] = $branches;

    	$current = array_filter($branches, function($item) {
            return $item[0] == '*';
        });
        $return['current'] = array_shift($current);

        return $return;
    }
}
