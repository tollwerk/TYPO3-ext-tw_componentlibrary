<?php

/**
 * data
 *
 * @category    Tollwerk
 * @package     Tollwerk\TwComponentlibrary
 * @subpackage  Tollwerk\TwComponentlibrary\Component
 * @author      Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright   Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license     http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2019 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

/**
 * Preview template interface
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 */
interface TemplateInterface
{
    /**
     * Serialize the template
     *
     * @return string Serialized template
     */
    public function __toString();

    /**
     * Add a CSS stylesheet
     *
     * @param string $url CSS stylesheet URL
     */
    public function addStylesheet($url);

    /**
     * Add a header JavaScript
     *
     * @param string $url Header JavaScript URL
     */
    public function addHeaderScript($url);

    /**
     * Add a header inclusion resource
     *
     * @param string $path Header inclusion path
     */
    public function addHeaderInclude($path);

    /**
     * Add a footer JavaScript
     *
     * @param string $path Footer JavaScript URL
     */
    public function addFooterScript($url);

    /**
     * Add a footer inclusion resource
     *
     * @param string $path Footer inclusion path
     */
    public function addFooterInclude($path);

    /**
     * Return all template resources
     *
     * @return TemplateResources Template resources
     */
    public function getTemplateResources();

    /**
     * Add common stylesheets
     *
     * @param string $commonStylesheets Common stylesheets
     */
    public static function addCommonStylesheets($commonStylesheets);

    /**
     * Add common header scripts
     *
     * @param string $commonHeaderScripts Common header scripts
     */
    public static function addCommonHeaderScripts($commonHeaderScripts);

    /**
     * Add common footer scripts
     *
     * @param string $commonFooterScripts Common footer scripts
     */
    public static function addCommonFooterScripts($commonFooterScripts);
}
