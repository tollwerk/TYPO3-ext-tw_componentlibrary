<?php

/**
 * Abstract component base
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

use Tollwerk\TwComponentlibrary\Component\Preview\FluidTemplate;
use Tollwerk\TwComponentlibrary\Component\Preview\TemplateInterface;
use Tollwerk\TwComponentlibrary\Component\Preview\TemplateResources;
use Tollwerk\TwComponentlibrary\Utility\TypoScriptUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use XhtmlFormatter\Formatter;

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
     * Page ID for this request
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
    protected $name = null;
    /**
     * Variant
     *
     * @var string
     */
    protected $variant = null;
    /**
     * Alternative label
     *
     * @var string
     */
    protected $label = null;
    /**
     * Extension key
     *
     * @var string
     */
    protected $extensionKey;
    /**
     * Extbase extension name
     *
     * @var string
     */
    protected $extensionName;
    /**
     * Component path
     *
     * @var array
     */
    protected $componentPath = [];
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
     * List of components dependencies
     *
     * @var array
     */
    protected $dependencies = [];
    /**
     * Controller context
     *
     * @var null|ControllerContext
     */
    protected $controllerContext;

    /**
     * Component constructor
     * @param ControllerContext|null $controllerContext Controller context
     */
    public function __construct(ControllerContext $controllerContext = null)
    {
        $this->controllerContext = $controllerContext;
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->request = $this->objectManager->get(Request::class);
        $this->preview = new FluidTemplate($this->getDependencyTemplateResources());

        $this->determineExtensionName();
        $this->determineNameAndVariant();

        $this->initialize();
        $this->configure();
    }

    /**
     * Get the template resources of component dependencies
     *
     * @return TemplateResources[] Component dependency templace resources
     */
    protected function getDependencyTemplateResources()
    {
        $templateResources = [];

        // Run through all component dependencies
        foreach ($this->dependencies as $dependency) {
            $templateResources[] = $this->objectManager->get($dependency)->getPreviewTemplateResources();
        }

        return $templateResources;
    }

    /**
     * Find the extension name the current component belongs to
     *
     * @throws \RuntimeException If the component path is invalid
     */
    protected function determineExtensionName()
    {
        $reflectionClass = new \ReflectionClass($this);
        $componentFilePath = dirname($reflectionClass->getFileName());

        // If the file path is invalid
        $extensionDirPosition = strpos($componentFilePath, 'ext'.DIRECTORY_SEPARATOR);
        if ($extensionDirPosition === false) {
            throw new \RuntimeException('Invalid component path', 1481360976);
        }

        // Extract the extension key
        $componentPath = explode(
            DIRECTORY_SEPARATOR,
            substr($componentFilePath, $extensionDirPosition + strlen('ext'.DIRECTORY_SEPARATOR))
        );
        $extensionKey = array_shift($componentPath);

        // If the extension is unknown
        if (!in_array($extensionKey, ExtensionManagementUtility::getLoadedExtensionListArray())) {
            throw new \RuntimeException(sprintf('Unknown extension key "%s"', $extensionKey), 1481361198);
        }

        // Register the extension key & name
        $this->extensionKey = $extensionKey;
        $this->extensionName = GeneralUtility::underscoredToUpperCamelCase($extensionKey);

        // Process the component path
        if (array_shift($componentPath) !== 'Components') {
            throw new \RuntimeException('Invalid component path', 1481360976);
        }
        $this->componentPath = array_map([static::class, 'expandComponentName'], $componentPath);
    }

    /**
     * Determine the component name and variant
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
        return trim(
            implode(
                ' ',
                array_map(
                    'ucwords', preg_split('/_+/', GeneralUtility::camelCaseToLowerCaseUnderscored($componentPath))
                )
            )
        ) ?: null;
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
        $this->addDocumentation();
    }

    /**
     * Add an external documentation
     */
    protected function addDocumentation()
    {
        $docDirectory = $this->getDocumentationDirectory();

        // If there's a documentation directory
        if (is_dir($docDirectory)) {
            $validIndexDocuments = [
                'index.md',
                'readme.md',
                strtolower($this->name.'.md')
            ];
            $indexDocument = null;
            $documents = [];

            // Run through all documentation files
            foreach (scandir($docDirectory) as $document) {
                if (!is_file($docDirectory.DIRECTORY_SEPARATOR.$document)) {
                    continue;
                }

                // If there's a valid documentation index file
                if (in_array(strtolower($document), $validIndexDocuments)) {
                    if ($indexDocument === null) {
                        $indexDocument = $docDirectory.DIRECTORY_SEPARATOR.$document;
                    }
                    continue;
                }

                $documents[] = $docDirectory.DIRECTORY_SEPARATOR.$document;
            }

            // If there's an index document
            if ($indexDocument !== null) {
                $this->addNotice(file_get_contents($indexDocument));
                return;
            }

            // If there are remaining documents: Auto-create a simple listing
            if (count($documents)) {
                $listing = [];

                // Run through all documents
                foreach ($documents as $document) {
                    $extension = strtolower(pathinfo($document, PATHINFO_EXTENSION));
                    $listing[] = '* '.(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg']) ? '!' : '').
                        '['.pathinfo($document, PATHINFO_FILENAME).']('.basename($document).')';
                }

                $this->addNotice(implode(PHP_EOL, $listing));
            }
        }
    }

    /**
     * Return the documentation directory for this component
     *
     * @param bool $rootRelative Return a root relative path
     * @return string Documentation directory
     */
    protected function getDocumentationDirectory($rootRelative = false)
    {
        $reflectionObject = new \ReflectionObject($this);
        $componentFile = $reflectionObject->getFileName();
        $docDirectory = dirname($componentFile).DIRECTORY_SEPARATOR.$this->name;
        return $rootRelative ? substr($docDirectory, strlen(PATH_site) - 1) : $docDirectory;
    }

    /**
     * Add a notice
     *
     * @param string $notice Notice
     */
    protected function addNotice($notice)
    {
        if (!$this->variant) {
            $notice = trim($notice);
            $this->notice = strlen($notice) ? $this->exportNotice($notice) : null;
        }
    }

    /**
     * Export a notice
     *
     * @param $notice
     * @return mixed
     */
    protected function exportNotice($notice)
    {
        $docDirectoryPath = strtr($this->getDocumentationDirectory(true), [DIRECTORY_SEPARATOR => '/']).'/';
        return preg_replace_callback('/\[([^\]]*?)\]\(([^\)]*?)\)/', function ($match) use ($docDirectoryPath) {
            return '['.$match[1].']('
                .(preg_match('%^https?\:\/\/%i', $match[2]) ? '' : $docDirectoryPath)
                .$match[2].')';
        }, $notice);
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
            'label' => $this->label,
            'class' => get_class($this),
            'type' => $this->type,
            'valid' => false,
            'path' => $this->componentPath,
            'docs' => $this->getDocumentationDirectory(),
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
    public function getRequestArguments()
    {
        return $this->exportRequest()['arguments'];
    }

    /**
     * Return a list of component dependencies
     *
     * @return array Component dependencies
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Return the preview template resources
     *
     * @return TemplateResources Preview template resources
     */
    public function getPreviewTemplateResources()
    {
        return $this->preview->getTemplateResources();
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
     * Set a preview template
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
     * Initialize a global Frontend renderer and return a content object renderer instance
     *
     * @return ContentObjectRenderer Content object renderer
     * @throws \Exception
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     */
    protected function initializeTSFE()
    {
        $GLOBALS['TSFE'] = TypoScriptUtility::getTSFE($this->page, $this->typeNum);
        $GLOBALS['TSFE']->cObj = new ContentObjectRenderer($GLOBALS['TSFE']);
        $GLOBALS['TSFE']->cObj->start($GLOBALS['TSFE']->page, 'pages');
        return $GLOBALS['TSFE']->cObj;
    }

    /**
     * Beautify HTML source
     *
     * @param string $html HTML source code
     * @return string Beautified HTML source code
     */
    protected function beautify($html)
    {
        $formatter = new Formatter();
        return $formatter->format($html);
    }
}
