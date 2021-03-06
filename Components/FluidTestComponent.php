<?php

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

namespace Tollwerk\TwComponentlibrary\Component;

/**
 * FluidTest component
 */
class FluidTestComponent extends FluidTemplateComponent
{
    /**
     * Label
     *
     * @var string
     */
    protected $label = 'FluidTest';
    /**
     * Component status
     *
     * @var int
     */
    protected $status = self::STATUS_TBD;
    /**
     * Development component
     *
     * @var bool
     */
    const DEVELOPMENT = true;

    /**
     * Configure the component
     *
     * @return void
     */
    protected function configure()
    {
        $this->setTemplate('EXT:tw_componentlibrary/Resources/Private/Partials/Test/Fluid.html');
        $this->addResource('EXT:tw_componentlibrary/Resources/Public/Icons/Fractal.svg');
        $this->preview->addStylesheet('EXT:tw_componentlibrary/Resources/Public/Test/Component.min.css');
        $this->preview->addHeaderScript('EXT:tw_componentlibrary/Resources/Public/Test/Component.critical.min.js');
        $this->preview->addFooterScript('EXT:tw_componentlibrary/Resources/Public/Test/Component.default.min.js');
    }
}
