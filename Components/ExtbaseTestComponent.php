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

use ReflectionException;
use Tollwerk\TwComponentlibrary\Controller\ComponentController;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidActionNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidControllerNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * ExtbaseTest component
 */
class ExtbaseTestComponent extends ExtbaseComponent
{
    /**
     * Label
     *
     * @var string
     */
    protected $label = 'ExtbaseTest';
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
     * @throws ReflectionException
     * @throws InvalidActionNameException
     * @throws InvalidArgumentNameException
     * @throws InvalidControllerNameException
     * @throws InvalidExtensionNameException
     * @throws Exception
     */
    protected function configure()
    {
        $this->setExtbaseConfiguration('Component', ComponentController::class, 'render', 'TwComponentlibrary');
        $this->setControllerActionArgument('name', 'value');
    }
}
