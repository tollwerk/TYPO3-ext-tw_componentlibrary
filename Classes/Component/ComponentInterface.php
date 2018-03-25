<?php

/**
 * Component interface
 *
 * @category Tollwerk
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 * @author Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2018 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  Copyright © 2018 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***********************************************************************************/

namespace Tollwerk\TwComponentlibrary\Component;

/**
 * Component interface
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 */
interface ComponentInterface
{
    /**
     * Component is to be done
     *
     * @var int
     */
    const STATUS_TBD = 'tbd';
    /**
     * Component is work in progress
     *
     * @var int
     */
    const STATUS_WIP = 'wip';
    /**
     * Component is in draft state
     *
     * @var int
     */
    const STATUS_DRAFT = 'draft';
    /**
     * Component is a prototype
     *
     * @var int
     */
    const STATUS_PROTOTYPE = 'prototype';
    /**
     * Component is ready
     *
     * @var int
     */
    const STATUS_READY = 'ready';
    /**
     * Fluid component type
     *
     * @var string
     */
    const TYPE_FLUID = 'fluid';
    /**
     * TypoScript component type
     *
     * @var string
     */
    const TYPE_TYPOSCRIPT = 'typoscript';
    /**
     * Extbase component type
     *
     * @var string
     */
    const TYPE_EXTBASE = 'extbase';
    /**
     * Content component type
     *
     * @var string
     */
    const TYPE_CONTENT = 'content';
    /**
     * Form component type
     *
     * @var string
     */
    const TYPE_FORM = 'form';
    /**
     * Valid component types
     *
     * @var array
     */
    const TYPES = [
        self::TYPE_FLUID,
        self::TYPE_FORM,
        self::TYPE_TYPOSCRIPT,
        self::TYPE_EXTBASE,
        self::TYPE_CONTENT
    ];

    /**
     * Export the component's properties
     *
     * @return array Properties
     */
    public function export();

    /**
     * Prepare a component path
     *
     * @param string $componentPath Component path
     * @return string Component name
     */
    public static function expandComponentName($componentPath);

    /**
     * Render this component
     *
     * @return string Rendered component (HTML)
     */
    public function render();

    /**
     * Return a list of component dependencies
     *
     * @return array Component dependencies
     */
    public function getDependencies();

    /**
     * Return the preview template resources
     *
     * @return TemplateResources Preview template resources
     */
    public function getPreviewTemplateResources();
}
