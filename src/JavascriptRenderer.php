<?php

namespace BranchSwitcher;

use BranchSwitcher\BranchSwitcher;

class JavascriptRenderer
{
    protected $variableName = 'BranchSwitcher';

    protected $cssFiles = ['branch-switcher.css'];
    protected $jsFiles = ['branch-switcher.js'];

    protected $basePath;

    const RELATIVE_PATH = 'path';
    const RELATIVE_URL = 'url';

    const INITIALIZE_CONSTRUCTOR = 2;
    const INITIALIZE_CONTROLS = 4;

    public function __construct(BranchSwitcher $branchSwitcher, $baseUrl = null, $basePath = null)
    {
        $this->branchSwitcher = $branchSwitcher;

        if ($baseUrl === null) {
            $baseUrl = '/vendor/guido/branch-switcher/resources';
        }
        $this->baseUrl = $baseUrl;

        if ($basePath === null) {
            $basePath = realpath(__DIR__ . '/../resources');
        }
        $this->basePath = $basePath;

        // bitwise operations cannot be done in class definition :(
        $this->initialization = self::INITIALIZE_CONSTRUCTOR | self::INITIALIZE_CONTROLS;
    }

    /**
     * {@inheritdoc}
     */
    public function renderHead()
    {
        $cssRoute = route('branch-switcher.assets.css', [
            'v' => $this->getModifiedTime('css'),
        ]);
        $jsRoute = route('branch-switcher.assets.js', [
            'v' => $this->getModifiedTime('js'),
        ]);

        $cssRoute = preg_replace('/\Ahttps?:/', '', $cssRoute);
        $jsRoute  = preg_replace('/\Ahttps?:/', '', $jsRoute);

        $html  = "<link rel='stylesheet' type='text/css' property='stylesheet' href='{$cssRoute}'>";
        $html .= "<script type='text/javascript' src='{$jsRoute}'></script>";

        $html .= $this->getInlineHtml();

        return $html;
    }

    protected function getInlineHtml()
    {
        $html = '';

        foreach (['head', 'css', 'js'] as $asset) {
            foreach ($this->getAssets('inline_' . $asset) as $item) {
                $html .= $item . "\n";
            }
        }

        return $html;
    }

    /**
     * Returns the list of asset files
     *
     * @param string $type 'css', 'js', 'inline_css', 'inline_js', 'inline_head', or null for all
     * @param string $relativeTo The type of path to which filenames must be relative (path, url or null)
     * @return array
     */
    public function getAssets($type = null, $relativeTo = self::RELATIVE_PATH)
    {
        $cssFiles = $this->cssFiles;
        $jsFiles = $this->jsFiles;
        $inlineCss = array();
        $inlineJs = array();
        $inlineHead = array();

        if ($relativeTo) {
            $root = $this->getRelativeRoot($relativeTo, $this->basePath, $this->baseUrl);
            $cssFiles = $this->makeUriRelativeTo($cssFiles, $root);
            $jsFiles = $this->makeUriRelativeTo($jsFiles, $root);
        }

        // Deduplicate files
        $cssFiles = array_unique($cssFiles);
        $jsFiles = array_unique($jsFiles);

        return $this->filterAssetArray(array($cssFiles, $jsFiles, $inlineCss, $inlineJs, $inlineHead), $type);
    }

    /**
     * Returns the correct base according to the type
     *
     * @param string $relativeTo
     * @param string $basePath
     * @param string $baseUrl
     * @return string
     */
    protected function getRelativeRoot($relativeTo, $basePath, $baseUrl)
    {
        if ($relativeTo === self::RELATIVE_PATH) {
            return $basePath;
        }
        if ($relativeTo === self::RELATIVE_URL) {
            return $baseUrl;
        }
        return null;
    }

    /**
     * Get the last modified time of any assets.
     *
     * @param string $type 'js' or 'css'
     * @return int
     */
    protected function getModifiedTime($type)
    {
        $files = $this->getAssets($type);

        $latest = 0;
        foreach ($files as $file) {
            $mtime = filemtime($file);
            if ($mtime > $latest) {
                $latest = $mtime;
            }
        }
        return $latest;
    }

    /**
     * Return assets as a string
     *
     * @param string $type 'js' or 'css'
     * @return string
     */
    public function dumpAssetsToString($type)
    {
        $files = $this->getAssets($type);

        $content = '';
        foreach ($files as $file) {
            $content .= file_get_contents($file) . "\n";
        }

        return $content;
    }

    /**
     * Makes a URI relative to another
     *
     * @param string|array $uri
     * @param string $root
     * @return string
     */
    protected function makeUriRelativeTo($uri, $root)
    {
        if (!$root) {
            return $uri;
        }

        if (is_array($uri)) {
            $uris = [];
            foreach ($uri as $u) {
                $uris[] = $this->makeUriRelativeTo($u, $root);
            }
            return $uris;
        }

        if (substr($uri, 0, 1) === '/' || preg_match('/^([a-zA-Z]+:\/\/|[a-zA-Z]:\/|[a-zA-Z]:\\\)/', $uri)) {
            return $uri;
        }
        return rtrim($root, '/') . "/$uri";
    }

    /**
     * Filters a tuple of (css, js, inline_css, inline_js, inline_head) assets according to $type
     *
     * @param array $array
     * @param string $type 'css', 'js', 'inline_css', 'inline_js', 'inline_head', or null for all
     * @return array
     */
    protected function filterAssetArray($array, $type = null)
    {
        $types = array('css', 'js', 'inline_css', 'inline_js', 'inline_head');
        $typeIndex = array_search(strtolower($type), $types);

        return $typeIndex !== false ? $array[$typeIndex] : $array;
    }

    /**
     * Returns the code needed to display the branch switcher
     *
     * AJAX request should not render the initialization code.
     *
     * @param boolean $initialize Whether or not to render the branch switcher initialization code
     * @return string
     */
    public function render($initialize = true)
    {
        $js = $initialize
                ? $this->getJsInitializationCode()
                : '';

        return "<script type=\"text/javascript\">\n$js\n</script>\n";
    }

    /**
     * Returns the js code needed to initialize the branch switcher
     *
     * @return string
     */
    protected function getJsInitializationCode()
    {
        $js = '';
        if (($this->initialization & self::INITIALIZE_CONTROLS) !== self::INITIALIZE_CONTROLS) {
            return $js;
        }

        $branches = getBranches();

        if ($branches['error']) {
            $options['error'] = $branches['error'];
        } else {
            $options = [
                'endpoint' => app('config')->get('branch-switcher.route_prefix') . '/switch',
                'branches' => $branches['all'],
                'activeBranch' => $branches['current'],
                'commands' => app('config')->get('branch-switcher.commands'),
            ];
        }

        $js .= sprintf("%s.setData(%s);\n", $this->variableName, json_encode($options));
        $js .= sprintf("%s.run();\n", $this->variableName);

        return $js;
    }
}
