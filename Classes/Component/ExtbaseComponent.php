<?php

/**
 * Extbase component
 *
 * @category Tollwerk
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 * @author Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Response;

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
    protected $type = self::TYPE_EXTBASE;
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
     * Extbase controller class
     *
     * @var string
     */
    protected $extbaseControllerClass = null;
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
     * Controller instance
     *
     * @var ActionController|ComponentControllerInterface
     */
    protected $controllerInstance = null;
    /**
     * Prefix for controller argument requests
     *
     * @var string
     */
    protected $controllerArgumentRequestPrefix = null;
    /**
     * Controller settings
     *
     * @var array
     */
    protected $controllerSettings = [];

    /**
     * Initialize the component
     *
     * @return void
     */
    protected function initialize()
    {
        parent::initialize();

        // Register the default extbase extension name
        $this->extbaseExtensionName = GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName);
    }

    /**
     * Set the extbase configuration
     *
     * @param string $pluginName Plugin name
     * @param string $controllerClass Controller name
     * @param string $actionName Action name
     * @param string|null $extensionName Extension name
     */
    public function setExtbaseConfiguration($pluginName, $controllerClass, $actionName, $extensionName = null)
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
        $controllerClass = trim($controllerClass);
        if (empty($controllerClass)
            || !class_exists($controllerClass)
            || !($controllerReflection = new \ReflectionClass($controllerClass))->implementsInterface(
                ControllerInterface::class
            )
        ) {
            throw new \RuntimeException(sprintf('Invalid controller class "%s"', $controllerClass), 1481646376);
        }
        $this->extbaseControllerClass = $controllerClass;
        $this->extbaseController = preg_replace('/Controller$/', '', $controllerReflection->getShortName());

        // Validate the controller action
        $actionName = trim($actionName);
        if (empty($actionName) || !is_callable([$this->extbaseControllerClass, $actionName.'Action'])) {
            throw new \RuntimeException(sprintf('Invalid controller action "%s"', $actionName), 1481646569);
        }
        $this->extbaseAction = $actionName;

        // Construct the controller argument request prefix
        $this->controllerArgumentRequestPrefix = 'tx_'.strtolower(str_replace('_', '', $this->extbaseExtensionName)).
            '_'.strtolower($this->extbasePlugin);

        // Construct and set the controller request arguments
        $this->request->setControllerObjectName($this->extbaseControllerClass);
        $this->request->setControllerExtensionName($this->extbaseExtensionName);
        $this->request->setControllerName($this->extbaseController);
        $this->request->setControllerActionName($this->extbaseAction);
        $this->request->setPluginName($this->extbasePlugin);
        $this->request->setArgument(
            $this->controllerArgumentRequestPrefix,
            [
                'controller' => $this->extbaseController,
                'action' => $this->extbaseAction,
            ]
        );
    }

    /**
     * Set a controller action argument
     *
     * @param string $name Argument name
     * @param mixed $value Argument value
     */
    public function setControllerActionArgument($name, $value)
    {
        // Validate the argument name
        $name = trim($name);
        if (empty($name)) {
            throw new \RuntimeException('Invalid extbase controller argument name', 1481708515);
        }

        $this->request->setArgument($name, $value);
    }

    /**
     * Set the controller settings
     *
     * @param array $settings Controller settings
     */
    public function setControllerSettings(array $settings)
    {
        $this->controllerSettings = $settings;
    }

    /**
     * Return component specific properties
     *
     * @return array Component specific properties
     */
    protected function exportInternal()
    {
        // Compose a configuration string
        if ($this->extbaseExtensionName && $this->extbasePlugin && $this->extbaseController && $this->extbaseAction) {
            $this->config = [
                'extension' => $this->extbaseExtensionName,
                'plugin' => $this->extbasePlugin,
                'controller' => $this->extbaseController,
                'action' => $this->extbaseAction,
                'settings' => $this->controllerSettings,
            ];

            $controllerInstance = $this->getControllerInstance();
            $controllerInstance->skipActionCall(true);

            /** @var \TYPO3\CMS\Extbase\Mvc\Web\Response $response */
            $response = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Response');
            $controllerInstance->processRequest($this->request, $response);

            $this->template = $controllerInstance->getView()
                ->getComponentTemplate($this->extbaseController, $this->extbaseAction);
        }

        return parent::exportInternal();
    }

    /**
     * Render this component
     *
     * @return string Rendered component (HTML)
     */
    public function render()
    {
        // Set the request arguments as GET parameters
        $_GET = $this->getRequestArguments();

        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Response $response */
        $response = $this->objectManager->get(Response::class);
        $this->getControllerInstance()->processRequest($this->request, $response);
        $result = $response->getContent();

        return $result;
    }

    /**
     * Return an extend Extbase controller instance
     *
     * @return ActionController|ComponentControllerInterface Extended Extbase controller instance
     */
    protected function getControllerInstance()
    {
        // One-time instantiation of an extended controller object
        if ($this->controllerInstance === null) {
            $extendedControllerClassName = $this->extbaseController.'ComponentController_'.md5($this->extbaseControllerClass);
            $extendedControllerPhp = 'class '.$extendedControllerClassName.' extends '.$this->extbaseControllerClass;
            $extendedControllerPhp .= ' implements '.ComponentControllerInterface::class;
            $extendedControllerPhp .= ' { use '.ComponentControllerTrait::class.'; }';
            eval($extendedControllerPhp);
            $this->controllerInstance = $this->objectManager->get($extendedControllerClassName);
        }

        return $this->controllerInstance->setSettings($this->controllerSettings);
    }
}
