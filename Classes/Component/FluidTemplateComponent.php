<?php

/**
 * FLUIDTEMPLATE component
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  Copyright © 2019 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

use Exception;
use ReflectionObject;
use RuntimeException;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Abstract FLUIDTEMPLATE component
 *
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 */
abstract class FluidTemplateComponent extends AbstractComponent
{
    /**
     * Component type
     *
     * @var string
     */
    protected $type = self::TYPE_FLUID;
    /**
     * Template
     *
     * @var string|null
     */
    protected $template = null;
    /**
     * Section
     *
     * @var string|null
     */
    protected $section = null;
    /**
     * Parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Render this component
     *
     * @return string Rendered component (HTML)
     */
    public function render()
    {
        // Set the request arguments as GET parameters
        $_GET = $this->getRequestArguments();

        try {

            // Instantiate a TypoScript parser
            $configurationManager = $this->objectManager->get(ConfigurationManager::class);
            $typoScriptParser     = GeneralUtility::makeInstance(TypoScriptParser::class);
            list(, $viewConfig) = $typoScriptParser->getVal(
                'plugin.tx_'.strtolower($this->extensionName).'.view',
                $GLOBALS['TSFE']->tmpl->setup
            );
            list(, $layoutRootPaths) = $typoScriptParser->getVal('layoutRootPaths', $viewConfig);
            list(, $templateRootPaths) = $typoScriptParser->getVal('templateRootPaths', $viewConfig);
            list(, $partialRootPaths) = $typoScriptParser->getVal('partialRootPaths', $viewConfig);

            /** @var StandaloneView $view */
            $view = $this->objectManager->get(StandaloneView::class);
            $view->getRenderingContext()->getVariableProvider()->add(
                'settings',
                $configurationManager->getConfiguration(
                    ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
                    $this->extensionName
                )
            );
            $view->setLayoutRootPaths($layoutRootPaths);
            $view->setTemplateRootPaths($templateRootPaths);
            $view->setPartialRootPaths($partialRootPaths);
            $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->template));
            $view->getRequest()->setControllerExtensionName($this->extensionName);
            $view->getRequest()->setOriginalRequestMappingResults($this->validationErrors);
            $view->assignMultiple($this->parameters);
            $view->assignMultiple($this->parameters);
            $result = $this->beautify(
                $this->section ? $view->renderSection($this->section, $this->parameters) : $view->render()
            );

            // In case of an error
        } catch (Exception $e) {
            $result = '<pre class="error"><strong>'.$e->getMessage().'</strong>'.PHP_EOL
                      .$e->getTraceAsString().'</pre>';
        }

        return $result;
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
        parent::initialize();
        $this->loadJsonParameters();
    }

    /**
     * Load parameters provided from an external JSON file
     */
    protected function loadJsonParameters()
    {
        $reflectionObject = new ReflectionObject($this);
        $componentFile    = $reflectionObject->getFileName();
        $parameterFile    = dirname($componentFile).DIRECTORY_SEPARATOR.pathinfo(
                $componentFile,
                PATHINFO_FILENAME
            ).'.json';
        if (is_readable($parameterFile)) {
            $jsonParameters = file_get_contents($parameterFile);
            if (strlen($jsonParameters)) {
                $jsonParameterObj = @json_decode($jsonParameters);
                if ($jsonParameterObj && is_object($jsonParameterObj)) {
                    foreach ($jsonParameterObj as $name => $value) {
                        $this->setParameter($name, $value);
                    }
                }
            }
        }
    }

    /**
     * Set a rendering parameter
     *
     * @param string $param Parameter name
     * @param mixed $value  Parameter value
     *
     * @throws RuntimeException If the parameter name is invalid
     */
    protected function setParameter($param, $value)
    {
        $param = trim($param);
        if (!strlen($param)) {
            throw new RuntimeException(sprintf('Invalid fluid template parameter "%s"', $param), 1481551574);
        }

        $this->parameters[$param] = $value;
    }

    /**
     * Set the fluid template
     *
     * @param string $template Fluid template
     */
    protected function setTemplate($template)
    {
        $this->template = trim($template) ?: null;
    }

    /**
     * Set the fluid template section
     *
     * @param string $section Fluid template section
     */
    protected function setSection($section)
    {
        $this->section = trim($section) ?: null;
    }

    /**
     * Return component specific properties
     *
     * @return array Component specific properties
     */
    protected function exportInternal()
    {
        // Read the linked TypoScript
        if ($this->template !== null) {
            $this->config = [
                'template' => $this->template,
                'section'  => $this->section,
            ];
            $templateFile = GeneralUtility::getFileAbsFileName($this->template);
            if (!strlen($templateFile) || !is_file($templateFile)) {
                throw new RuntimeException(sprintf('Invalid template file "%s"', $templateFile), 1481552328);
            }
            $this->template = file_get_contents($templateFile);
        }

        return array_merge(
            ['parameters' => $this->parameters],
            parent::exportInternal()
        );
    }
}
