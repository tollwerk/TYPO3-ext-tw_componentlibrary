<?php

/**
 * Abstract component
 *
 * @category Tollwerk
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 * @author Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2016 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  Copyright © 2016 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

use Tollwerk\TwComponentlibrary\Component\Preview\BasicTemplate;
use Tollwerk\TwComponentlibrary\Component\Preview\TemplateInterface;
use Tollwerk\TwComponentlibrary\Utility\EnvironmentService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Abstract component
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 */
abstract class AbstractComponent implements ComponentInterface
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * Environment service
     *
     * @var EnvironmentService
     */
    protected $environmentService;
    /**
     * Request
     *
     * @var Request
     */
    protected $request;
    /**
     * System language for this request
     *
     * @var int
     */
    protected $sysLanguage = 0;
    /**
     * Language parameter name
     *
     * @var string
     */
    protected $languageParameter = 'L';
    /**
     * Page ID for this requet
     *
     * @var int
     */
    protected $page = 1;
    /**
     * Type parameter for this request
     *
     * @var int
     */
    protected $typeNum = 0;
    /**
     * Component status
     *
     * @var int
     */
    protected $status = self::STATUS_WIP;
    /**
     * Name
     *
     * @var string
     */
    protected $name;
    /**
     * Variant
     *
     * @var string
     */
    protected $variant = null;
    /**
     * Extension name
     *
     * @var string
     */
    protected $extensionName;
    /**
     * Associated resources
     *
     * @var array
     */
    protected $resources = [];
    /**
     * Notice (Markdown)
     *
     * @var string
     */
    protected $notice = null;
    /**
     * Template text
     *
     * @var string
     */
    protected $template = '';
    /**
     * Preview template
     *
     * @var TemplateInterface|string
     */
    protected $preview = '';
    /**
     * Configuration
     *
     * @var string
     */
    protected $config = null;
    /**
     * Template file extensions
     *
     * @var string
     */
    protected $extension = 't3s';
    /**
     * Component type
     *
     * @var string
     */
    protected $type = 'abstract';

    /**
     * Component constructor
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->environmentService = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Service\\EnvironmentService');
        $this->request = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Request');
        $this->preview = new BasicTemplate();

        $this->determineExtensionName();
        $this->determineNameAndVariant();

        $this->initialize();
        $this->configure();
    }

    /**
     * Initialize the component
     *
     * Gets called immediately after construction. Override this method in components to initialize the component.
     *
     * @return void
     */
    protected function initialize()
    {

    }

    /**
     * Configure the component
     *
     * Gets called immediately after initialization. Override this method in components to set their properties.
     *
     * @return void
     */
    protected function configure()
    {

    }

    /**
     * Export the component's properties
     *
     * @return array Properties
     */
    final public function export()
    {
        $properties = [
            'status' => $this->status,
            'name' => $this->name,
            'variant' => $this->variant,
            'class' => get_class($this),
            'type' => $this->type,
            'valid' => false,
        ];

        // Export the component properties
        try {
            $properties = array_merge($properties, $this->exportInternal());
            $properties['request'] = $this->exportRequest();
            $properties['valid'] = true;

            // In case of an error
        } catch (\Exception $e) {
            $properties['error'] = $e->getMessage();
        }

        return $properties;
    }

    /**
     * Return component specific properties
     *
     * Override this method in sub classes to export specific properties.
     *
     * @return array Component specific properties
     */
    protected function exportInternal()
    {
        $properties = [];

        // Export the configuration & $template
        if (!$this->config) {
            throw new \RuntimeException('Invalid configuration', 1481363496);
        }

        $properties['config'] = $this->config;
        $properties['template'] = $this->template;
        $properties['extension'] = $this->extension;

        // Export the associated resources
        if (count($this->resources)) {
            $properties['resources'] = $this->resources;
        }

        // Export the notice
        if (strlen(trim($this->notice))) {
            $properties['notice'] = trim($this->notice);
        }

        // If this is the default variant
        if (!$this->variant) {
            $preview = trim(strval($this->preview));
            if (strlen($preview)) {
                $properties['preview'] = $preview;
            }
        }

        return $properties;
    }

    /**
     * Add an associated resource
     *
     * @param string $resource Associated resource
     */
    protected function addResource($resource)
    {
        $resource = trim($resource);
        if (strlen($resource)) {
            $this->resources[] = $resource;
        }
    }

    /**
     * Add a notice
     *
     * @param string $notice Notice
     */
    protected function addNotice($notice)
    {
        $notice = trim($notice);
        $this->notice = strlen($notice) ? $notice : null;
    }

    /**
     * Set a preview preview template
     *
     * @param TemplateInterface|string|null $preview Preview template
     */
    protected function setPreview($preview)
    {
        if (!($preview instanceof TemplateInterface) && !is_string($preview) && ($preview !== null)) {
            throw new \RuntimeException('Invalid preview preview', 1481368492);
        }
        $this->preview = $preview;
    }

    /**
     * Export the request options
     *
     * @return array
     */
    protected function exportRequest()
    {
        // Set the request language
        if ($this->languageParameter) {
            $this->request->setArgument($this->languageParameter, $this->sysLanguage);
        }

        $this->request->setControllerExtensionName($this->extensionName);

        // Set page ID and type
        $this->request->setArgument('id', $this->page);
        if (intval($this->typeNum)) {
            $this->request->setArgument('type', intval($this->typeNum));
        }

        return [
            'method' => $this->request->getMethod(),
            'arguments' => $this->request->getArguments(),
        ];
    }

    /**
     * Return the component's request arguments
     *
     * @return mixed Request arguments
     */
    public function getRequestArguments() {
        return $this->exportRequest()['arguments'];
    }

    /**
     * Find the extension name the current component belongs to
     *
     * @return string Extension name
     * @throws \RuntimeException If the component path is invalid
     */
    protected function determineExtensionName()
    {
        $reflectionClass = new \ReflectionClass($this);
        $componentFilePath = $reflectionClass->getFileName();

        // If the file path is invalid
        $extensionDirPosition = strpos($componentFilePath, 'ext'.DIRECTORY_SEPARATOR);
        if ($extensionDirPosition === false) {
            throw new \RuntimeException('Invalid component path', 1481360976);
        }

        // Extract the extension key
        list($extensionKey) = explode(
            DIRECTORY_SEPARATOR,
            substr($componentFilePath, $extensionDirPosition + strlen('ext'.DIRECTORY_SEPARATOR))
        );

        // If the extension is unknown
        if (!in_array($extensionKey, ExtensionManagementUtility::getLoadedExtensionListArray())) {
            throw new \RuntimeException(sprintf('Unknown extension key "%s"', $extensionKey), 1481361198);
        }

        $this->extensionName = GeneralUtility::underscoredToUpperCamelCase($extensionKey);
    }

    /**
     * Return the component name and variant
     *
     * @return array|null
     */
    protected function determineNameAndVariant()
    {
        $reflectionClass = new \ReflectionClass($this);
        $componentName = preg_replace('/Component$/', '', $reflectionClass->getShortName());
        list($name, $variant) = preg_split('/_+/', $componentName, 2);
        $this->name = self::expandComponentName($name);
        $this->variant = self::expandComponentName($variant);
    }


    /**
     * Prepare a component path
     *
     * @param string $componentPath Component path
     * @return string Component name
     */
    public static function expandComponentName($componentPath)
    {
        return trim(implode(
            ' ',
            array_map('ucwords', preg_split('/_+/', GeneralUtility::camelCaseToLowerCaseUnderscored($componentPath)))
        )) ?: null;
    }
}
