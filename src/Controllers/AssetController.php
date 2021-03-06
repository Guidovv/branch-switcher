<?php

namespace BranchSwitcher\Controllers;

use Illuminate\Http\Response;

class AssetController extends BaseController
{
    /**
     * Return the javascript for the BranchSwitcher
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function js()
    {
        $renderer = $this->branchSwitcher->getJavascriptRenderer();
        $content = $renderer->dumpAssetsToString('js');

        $response = new Response(
            $content,
            200,
            [
                'Content-Type' => 'text/javascript',
            ]
        );

        return $this->cacheResponse($response);
    }

    /**
     * Return the stylesheets for the BranchSwitcher
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function css()
    {
        $renderer = $this->branchSwitcher->getJavascriptRenderer();
        $content = $renderer->dumpAssetsToString('css');

        $response = new Response(
            $content,
            200,
            [
                'Content-Type' => 'text/css',
            ]
        );

        return $this->cacheResponse($response);
    }

    /**
     * Cache the response 1 year (31536000 sec)
     */
    protected function cacheResponse(Response $response)
    {
        $response->setSharedMaxAge(31536000);
        $response->setMaxAge(31536000);
        $response->setExpires(new \DateTime('+1 year'));

        return $response;
    }
}
