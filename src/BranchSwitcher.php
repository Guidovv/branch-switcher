<?php

namespace BranchSwitcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BranchSwitcher
{
    /**
     * The Laravel application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * True when booted.
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * True when enabled, false disabled an null for still unknown
     *
     * @var bool
     */
    protected $enabled = null;

    protected $jsRenderer = null;

    /**
     * @param Application $app
     */
    public function __construct($app = null)
    {
        if (! $app) {
            $app = app();   //Fallback when $app is not given
        }
        $this->app = $app;
        $this->version = $app->version();
    }

    /**
     * Boot the branchswitcher
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;
    }

    /**
     * Modify the response and inject the branch switcher
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  \Symfony\Component\HttpFoundation\Response $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modifyResponse(Request $request, Response $response)
    {
        if (!$this->isEnabled() || $this->isBranchSwitcherRequest()) {
            return $response;
        }

        $this->injectBranchSwitcher($response);

        return $response;
    }

    /**
     * Check if the BranchSwitcher is enabled
     * @return boolean
     */
    public function isEnabled()
    {
        if ($this->enabled === null) {
            $config = $this->app['config'];
            $configEnabled = $config->get('branch-switcher.enabled');
            $configEnvironments = $config->get('branch-switcher.environments') ?? [];

            $this->enabled = $configEnabled && in_array($config['app.env'], $configEnvironments) && ! $this->app->runningInConsole();
        }

        return $this->enabled;
    }

    /**
     * Check if this is a request to the BranchSwitcher
     *
     * @return bool
     */
    protected function isBranchSwitcherRequest()
    {
        return $this->app['request']->segment(1) == $this->app['config']->get('branch-switcher.route_prefix');
    }

    /**
     * Injects the branch switcher into the given Response.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response A Response instance
     * Based on https://github.com/symfony/WebProfilerBundle/blob/master/EventListener/WebDebugToolbarListener.php
     */
    public function injectBranchSwitcher(Response $response)
    {
        $content = $response->getContent();

        $renderer = $this->getJavascriptRenderer();
        $head = $renderer->renderHead();
        $widget = $renderer->render();

        // Try to put the js/css directly before the </head>
        $pos = strripos($content, '</head>');
        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $head . substr($content, $pos);
        } else {
            // Append the head before the widget
            $widget = $head . $widget;
        }

        // Try to put the widget at the end, directly before the </body>
        $pos = strripos($content, '</body>');
        $content = false !== $pos
                    ? substr($content, 0, $pos) . $widget . substr($content, $pos)
                    : $content . $widget;

        $original = null;
        if ($response instanceof \Illuminate\Http\Response && $response->getOriginalContent()) {
            $original = $response->getOriginalContent();
        }

        // Update the new content and reset the content length
        $response->setContent($content);
        $response->headers->remove('Content-Length');

        // Restore original response (eg. the View or Ajax data)
        if ($original) {
            $response->original = $original;
        }
    }

    /**
     * Returns a JavascriptRenderer for this instance
     *
     * @param string $baseUrl
     * @param string $basePathng
     * @return JavascriptRenderer
     */
    public function getJavascriptRenderer($baseUrl = null, $basePath = null)
    {
        if ($this->jsRenderer === null) {
            $this->jsRenderer = new JavascriptRenderer($this, $baseUrl, $basePath);
        }

        return $this->jsRenderer;
    }
}
