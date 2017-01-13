<?php

/**
 * data
 *
 * @category    Tollwerk
 * @package     Tollwerk\TwComponentlibrary
 * @subpackage  Tollwerk\TwComponentlibrary\Component
 * @author      Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright   Copyright © 2016 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license     http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2016 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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
     * JavaScripts to include in the header
     *
     * @var array
     */
    protected $headerScripts = [];
    /**
     * JavaScripts to include in the footer
     *
     * @var array
     */
    protected $footerScripts = [];

    /**
     * Serialize the template
     *
     * @return string Serialized template
     */
    public function __toString()
    {
        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>{{ _target.label }}</title>';

        // Include the registered CSS stylesheets
        foreach ($this->stylesheets as $cssUrl) {
            $html .= ' <link media="all" rel="stylesheet" href="{{ path \'/'.ltrim($cssUrl, '/').'\' }}">';
        }

        // Include the registered header JavaScripts
        foreach ($this->headerScripts as $jsUrl) {
            $html .= ' <script src="{{ path \'/'.ltrim($jsUrl, '/').'\' }}">';
        }

        $html .= '</head><body>{{{ yield }}}';

        // Include the registered footer JavaScripts
        foreach ($this->footerScripts as $jsUrl) {
            $html .= ' <script src="{{ path \'/'.ltrim($jsUrl, '/').'\' }}">';
        }

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
        $url = trim($url);
        if (strlen($url)) {
            $absStylesheet = GeneralUtility::getFileAbsFileName($url);
            $this->stylesheets[] = substr($absStylesheet, strlen(PATH_site));
        }
    }

    /**
     * Add a header JavaScript
     *
     * @param string $url Header JavaScript URL
     */
    public function addHeaderScript($url)
    {
        $url = trim($url);
        if (strlen($url)) {
            $absScript = GeneralUtility::getFileAbsFileName($url);
            if (is_file($absScript)) {
                $this->headerScripts[] = substr($absScript, strlen(PATH_site));
            }
        }
    }

    /**
     * Add a footer JavaScript
     *
     * @param string $url Footer JavaScript URL
     */
    public function addFooterScript($url)
    {
        $url = trim($url);
        if (strlen($url)) {
            $absScript = GeneralUtility::getFileAbsFileName($url);
            if (is_file($absScript)) {
                $this->footerScripts[] = substr($absScript, strlen(PATH_site));
            }
        }
    }
}
