<?php

/**
 * Extbase component
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

use Tollwerk\TwComponentlibrary\Utility\TypoScriptUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerInterface;

/**
 * Extbase component
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 */
class ExtbaseComponent extends AbstractComponent
{
    /**
     * Component type
     *
     * @var string
     */
    protected $type = 'extbase';
    /**
     * Extbase plubin name
     *
     * @var string
     */
    protected $extbasePlugin = null;
    /**
     * Extbase controller name
     *
     * @var string
     */
    protected $extbaseController = null;
    /**
     * Extbase action name
     *
     * @var string
     */
    protected $extbaseAction = null;
    /**
     * Extbase extension name
     *
     * @var string
     */
    protected $extbaseExtensionName = null;

    /**
     * Component constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->extbaseExtensionName = GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName);
    }

    /**
     * Set the extbase configuration
     *
     * @param string $pluginName Plugin name
     * @param string $controllerName Controller name
     * @param string $actionName Action name
     * @param string|null $extensionName Extension name
     */
    public function setExtbaseConfiguration($pluginName, $controllerName, $actionName, $extensionName = null)
    {
        // Validate the extension name
        if (!empty($extensionName)) {
            $extensionName = GeneralUtility::camelCaseToLowerCaseUnderscored(trim($extensionName));
            if (!in_array($extensionName, ExtensionManagementUtility::getLoadedExtensionListArray())) {
                throw new \RuntimeException(sprintf('Extension "%s" is not available', $extensionName), 1481645834);
            }
            $this->extbaseExtensionName = $extensionName;
        }

        // Validate the plugin name
        $pluginName = trim($pluginName);
        if (empty($pluginName)) {
            throw new \RuntimeException(sprintf('Invalid plugin name "%s"', $pluginName), 1481646376);
        }
        $this->extbasePlugin = $pluginName;

        // Validate the controller name
        $controllerName = trim($controllerName);
        if (empty($controllerName)
            || !class_exists($controllerName)
            || !(new \ReflectionClass($controllerName))->implementsInterface(ControllerInterface::class)
        ) {
            throw new \RuntimeException(sprintf('Invalid controller "%s"', $controllerName), 1481646376);
        }
        $this->extbaseController = $controllerName;

        // Validate the controller action
        $actionName = trim($actionName);
        if (empty($actionName) || !is_callable([$this->extbaseController, $actionName.'Action'])) {
            throw new \RuntimeException(sprintf('Invalid controller action "%s"', $actionName), 1481646569);
        }
        $this->extbaseAction = $actionName;
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
        // Compose a configuration string
        if ($this->extbaseExtensionName && $this->extbasePlugin && $this->extbaseController && $this->extbaseAction) {
            $this->config = 'EXT:'.$this->extbaseExtensionName;
            $this->config .= ':'.$this->extbasePlugin;
            $this->config .= ':'.$this->extbaseController;
            $this->config .= '->'.$this->extbaseAction.'Action';

            $this->template = 'EXTBASE';
        }

        return array_merge(
            [
                'extbase' => [
                    'extension' => $this->extbaseExtensionName,
                    'plugin' => $this->extbasePlugin,
                    'controller' => $this->extbaseController,
                    'action' => $this->extbaseAction,
                ]
            ],
            parent::exportInternal()
        );
    }

    /**
     * Render this component
     *
     * @return string Rendered component (HTML)
     */
    public function render()
    {
        return $this->type;

        // Simulate Frontend mode
        $this->environmentService->simulateFrontendMode(true);

        // Set the request arguments as GET parameters
        $_GET = $this->getRequestArguments();

        // Instantiate a frontend controller
        $TSFE = TypoScriptUtility::getTSFE($this->page, $this->typeNum);

        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->config));
        $view->assignMultiple($this->parameters);
        $result = $view->render();

        // Stop simulating Frontend mode
        $this->environmentService->simulateFrontendMode(false);

        return $result;
    }
}
