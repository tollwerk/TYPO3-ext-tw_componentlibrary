<?php

/**
 * data
 *
 * @category    Tollwerk
 * @package     Tollwerk\TwComponentlibrary
 * @subpackage  Tollwerk\TwComponentlibrary\Component
 * @author      Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright   Copyright © 2017 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license     http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of
 *  this software and associated documentation files (the "Software"), to deal in
 *  the Software without restriction, including without limitation the rights to
 *  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 *  the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ***********************************************************************************/

namespace Tollwerk\TwComponentlibrary\Component\Preview;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Basic preview template
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 */
class BasicTemplate implements TemplateInterface
{
    /**
     * CSS stylesheets
     *
     * @var array
     */
    protected $stylesheets = [];
    /**
     * Common CSS stylesheets
     *
     * @var array
     */
    protected static $commonStylesheets = [];
    /**
     * JavaScripts to include in the header
     *
     * @var array
     */
    protected $headerScripts = [];
    /**
     * Common header scripts
     *
     * @var array
     */
    protected static $commonHeaderScripts = [];
    /**
     * Resources to be included in the file header
     *
     * @var array
     */
    protected $headerIncludes = [];
    /**
     * JavaScripts to include in the footer
     *
     * @var array
     */
    protected $footerScripts = [];
    /**
     * Common footer scripts
     *
     * @var array
     */
    protected static $commonFooterScripts = [];
    /**
     * Resources to be included in the file footer
     *
     * @var array
     */
    protected $footerIncludes = [];

    /**
     * Constructor
     *
     * @param TemplateResources[] $templateResources Base template resources
     */
    public function __construct(array $templateResources = [])
    {
        // Run through all base template resources
        /** @var TemplateResources $templateResource */
        foreach ($templateResources as $templateResource) {
            $this->mergeTemplateResources($templateResource);
        }
    }

    /**
     * Serialize the template
     *
     * @return string Serialized template
     */
    public function __toString()
    {
        $html = '<!DOCTYPE html><html lang="en"><head><script>document.documentElement.className+=\'js\'</script>';
        $html .= implode('', $this->headerIncludes);
        $html .= '<meta charset="UTF-8"><title>{{ _target.label }} — Preview Layout</title>';

        // Include the registered CSS stylesheets
        foreach (array_unique(array_merge(self::$commonStylesheets, $this->stylesheets)) as $cssUrl) {
            if (preg_match('%^https?\:\/\/%', $cssUrl)) {
                $html .= ' <link media="all" rel="stylesheet" href="'.htmlspecialchars($cssUrl).'">';
            } else {
                $html .= ' <link media="all" rel="stylesheet" href="{{ path \'/'.ltrim($cssUrl, '/').'\' }}">';
            }
        }

        // Include the registered header JavaScripts
        foreach (array_unique(array_merge(self::$commonHeaderScripts, $this->headerScripts)) as $jsUrl) {
            if (preg_match('%^https?\:\/\/%', $jsUrl)) {
                $html .= ' <script src="'.htmlspecialchars($jsUrl).'"></script>';
            } else {
                $html .= ' <script src="{{ path \'/'.ltrim($jsUrl, '/').'\' }}"></script>';
            }
        }

        $html .= '</head><body>{{{ yield }}}';

        // Include the registered footer JavaScripts
        foreach (array_unique(array_merge(self::$commonFooterScripts, $this->footerScripts)) as $jsUrl) {
            if (preg_match('%^https?\:\/\/%', $jsUrl)) {
                $html .= ' <script src="'.htmlspecialchars($jsUrl).'"></script>';
            } else {
                $html .= ' <script src="{{ path \'/'.ltrim($jsUrl, '/').'\' }}" async defer></script>';
            }
        }

        $html .= implode('', $this->footerIncludes);
        $html .= '</body></html>';
        return $html;
    }

    /**
     * Add a CSS stylesheet
     *
     * @param string $url CSS stylesheet URL
     */
    public function addStylesheet($url)
    {
        $url = self::resolveUrl($url);
        if ($url) {
            $this->stylesheets[self::hashResource($url)] = $url;
        }
    }

    /**
     * Add a header JavaScript
     *
     * @param string $url Header JavaScript URL
     */
    public function addHeaderScript($url)
    {
        $url = self::resolveUrl($url);
        if ($url) {
            $this->headerScripts[self::hashResource($url)] = $url;
        }
    }

    /**
     * Add a header inclusion resource
     *
     * @param string $path Header inclusion path
     */
    public function addHeaderInclude($path)
    {
        $path = trim($path);
        if (strlen($path)) {
            $absPath = GeneralUtility::getFileAbsFileName($path);
            if (is_file($absPath)) {
                $include = file_get_contents($absPath);
                $this->headerIncludes[self::hashResource($include)] = $include;
            }
        }
    }

    /**
     * Add a footer JavaScript
     *
     * @param string $path Footer JavaScript URL
     */
    public function addFooterScript($url)
    {
        $url = self::resolveUrl($url);
        if ($url) {
            $this->footerScripts[self::hashResource($url)] = $url;
        }
    }

    /**
     * Add a footer inclusion resource
     *
     * @param string $path Footer inclusion path
     */
    public function addFooterInclude($path)
    {
        $path = trim($path);
        if (strlen($path)) {
            $absPath = GeneralUtility::getFileAbsFileName($path);
            if (is_file($absPath)) {
                $include = file_get_contents($absPath);
                $this->footerIncludes[self::hashResource($include)] = $include;
            }
        }
    }

    /**
     * Add common stylesheets
     *
     * @param string $commonStylesheets Common stylesheets
     */
    public static function addCommonStylesheets($commonStylesheets)
    {
        foreach (GeneralUtility::trimExplode(',', $commonStylesheets, true) as $commonStylesheet) {
            $commonStylesheet = self::resolveUrl($commonStylesheet);
            if ($commonStylesheet) {
                self::$commonStylesheets[] = $commonStylesheet;
            }
        }
    }

    /**
     * Add common header scripts
     *
     * @param string $commonHeaderScripts Common header scripts
     */
    public static function addCommonHeaderScripts($commonHeaderScripts)
    {
        foreach (GeneralUtility::trimExplode(',', $commonHeaderScripts, true) as $commonHeaderScript) {
            $commonHeaderScript = self::resolveUrl($commonHeaderScript);
            if ($commonHeaderScript) {
                self::$commonHeaderScripts[] = $commonHeaderScript;
            }
        }
    }

    /**
     * Add common footer scripts
     *
     * @param string $commonFooterScripts Common footer scripts
     */
    public static function addCommonFooterScripts($commonFooterScripts)
    {
        foreach (GeneralUtility::trimExplode(',', $commonFooterScripts, true) as $commonFooterScript) {
            $commonFooterScript = self::resolveUrl($commonFooterScript);
            if ($commonFooterScript) {
                self::$commonFooterScripts[] = $commonFooterScript;
            }
        }
    }

    /**
     * Resolve a URL
     *
     * @param string $url URL
     * @return bool|string Resolved URL
     */
    protected static function resolveUrl($url)
    {
        $url = trim($url);
        if (strlen($url)) {
            if (preg_match('%^https?\:\/\/%', $url)) {
                return $url;
            }
            $absScript = GeneralUtility::getFileAbsFileName($url);
            if (is_file($absScript)) {
                return substr($absScript, strlen(PATH_site));
            }
        }
        return null;
    }

    /**
     * Return an MD5 hash for a resource
     *
     * @param string $resource Resource
     * @return string MD5 resource hash
     */
    protected static function hashResource($resource)
    {
        return md5($resource);
    }

    /**
     * Return all template resources
     *
     * @return TemplateResources Template resources
     */
    public function getTemplateResources()
    {
        return new TemplateResources(
            $this->stylesheets,
            $this->headerScripts,
            $this->headerIncludes,
            $this->footerScripts,
            $this->footerIncludes
        );
    }

    /**
     * Merge a set of template resources
     *
     * @param TemplateResources $templateResources Template resources
     */
    protected function mergeTemplateResources(TemplateResources $templateResources)
    {
        $this->stylesheets = array_merge($this->stylesheets, $templateResources->getStylesheets());
        $this->headerScripts = array_merge($this->headerScripts, $templateResources->getHeaderScripts());
        $this->headerIncludes = array_merge($this->headerIncludes, $templateResources->getHeaderIncludes());
        $this->footerScripts = array_merge($this->footerScripts, $templateResources->getFooterScripts());
        $this->footerIncludes = array_merge($this->footerIncludes, $templateResources->getFooterIncludes());
    }
}
