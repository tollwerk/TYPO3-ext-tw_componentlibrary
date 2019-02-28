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


class TemplateResources
{
    /**
     * Stylesheets
     *
     * @var array
     */
    protected $stylesheets = [];
    /**
     * Header scripts
     *
     * @var array
     */
    protected $headerScripts = [];
    /**
     * Header includes
     *
     * @var array
     */
    protected $headerIncludes = [];
    /**
     * Footer scripts
     *
     * @var array
     */
    protected $footerScripts = [];
    /**
     * Footer includes
     *
     * @var array
     */
    protected $footerIncludes = [];

    /**
     * Constructor
     *
     * @param array $stylesheets    Stylesheets
     * @param array $headerScripts  Header scripts
     * @param array $headerIncludes Header includes
     * @param array $footerScripts  Footer scripts
     * @param array $footerIncludes Footer includes
     */
    public function __construct(
        array $stylesheets,
        array $headerScripts,
        array $headerIncludes,
        array $footerScripts,
        array $footerIncludes
    ) {
        $this->stylesheets    = $stylesheets;
        $this->headerScripts  = $headerScripts;
        $this->headerIncludes = $headerIncludes;
        $this->footerScripts  = $footerScripts;
        $this->footerIncludes = $footerIncludes;
    }

    /**
     * Return the stylesheets
     *
     * @return array Stylesheets
     */
    public function getStylesheets()
    {
        return $this->stylesheets;
    }

    /**
     * Return the header scripts
     *
     * @return array Header scripts
     */
    public function getHeaderScripts()
    {
        return $this->headerScripts;
    }

    /**
     * Return the header includes
     *
     * @return array Header includes
     */
    public function getHeaderIncludes()
    {
        return $this->headerIncludes;
    }

    /**
     * Return the footer scripts
     *
     * @return array Footer scripts
     */
    public function getFooterScripts()
    {
        return $this->footerScripts;
    }

    /**
     * Return the footer includes
     *
     * @return array Footer includes
     */
    public function getFooterIncludes()
    {
        return $this->footerIncludes;
    }
}
