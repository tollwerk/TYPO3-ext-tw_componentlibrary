<?php

/**
 * Component command line controller
 *
 * @category    Tollwerk
 * @package     Tollwerk\TwComponentlibrary
 * @subpackage  Tollwerk\TwComponentlibrary\Command
 * @author      Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright   Copyright © 2017 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license     http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

namespace Tollwerk\TwComponentlibrary\Command;

use Tollwerk\TwComponentlibrary\Component\ComponentInterface;
use Tollwerk\TwComponentlibrary\Component\Preview\BasicTemplate;
use Tollwerk\TwComponentlibrary\Utility\Kickstarter;
use Tollwerk\TwComponentlibrary\Utility\Scanner;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Extbase\Mvc\Exception\CommandException;

/**
 * Component command controller
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Command
 * @cli
 */
class ComponentCommandController extends CommandController
{
    /**
     * Discover and extract all components
     */
    public function discoverCommand()
    {
        $setup = $this->objectManager->get(BackendConfigurationManager::class)->getTypoScriptSetup();
        /** @var TypoScriptService $typoscriptService */
        $typoscriptService = $this->objectManager->get(TypoScriptService::class);
        $config = $typoscriptService->convertTypoScriptArrayToPlainArray($setup['plugin.']['tx_twcomponentlibrary.']);

        // Register common stylesheets & scripts
        BasicTemplate::addCommonStylesheets($config['settings']['stylesheets']);
        BasicTemplate::addCommonHeaderScripts($config['settings']['headerScripts']);
        BasicTemplate::addCommonFooterScripts($config['settings']['footerScripts']);

        echo json_encode(Scanner::discover(), JSON_PRETTY_PRINT);
    }

    /**
     * Create a new component
     *
     * @param string $name Component path and name
     * @param string $type Component type
     * @param string $extension Host extension
     * @param string $vendor Host extension vendor name
     * @throws CommandException If the component name is empty / invalid
     * @throws CommandException If the component type is invalid
     * @throws CommandException If the provider extension is invalid
     * @throws CommandException If the provider extension vendor name is invalid
     */
    public function createCommand($name, $type, $extension = null, $vendor = null)
    {
        // Prepare the component name
        $name = GeneralUtility::trimExplode('/', $name, true);
        if (!count($name)) {
            throw new CommandException('Empty / invalid component name', 1507996606);
        }

        // Prepare the component type
        $type = strtolower($type);
        if (!in_array($type, ComponentInterface::TYPES)) {
            throw new CommandException(sprintf('Invalid component type "%s"', $type), 1507996917);
        }

        // Prepare the provider extension name
        $extension = trim($extension ?: $GLOBALS['TYPO3_CONF_VARS']['EXT']['extParams']['tw_componentlibrary']['defaultextension']);
        if (!strlen($extension) || !ExtensionManagementUtility::isLoaded($extension)) {
            throw new CommandException(sprintf('Invalid provider extension "%s"', $extension), 1507997408);
        }

        // Prepare the provider extension vendor name
        $vendor = trim($vendor ?: $GLOBALS['TYPO3_CONF_VARS']['EXT']['extParams']['tw_componentlibrary']['defaultvendor']);
        if (!strlen($vendor)) {
            throw new CommandException(sprintf('Invalid provider extension vendor name "%s"', $vendor), 1507998569);
        }

        Kickstarter::create($name, $type, $extension, $vendor);
    }
}
