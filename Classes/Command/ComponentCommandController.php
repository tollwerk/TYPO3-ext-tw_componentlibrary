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

use Tollwerk\TwComponentlibrary\Component\Preview\BasicTemplate;
use Tollwerk\TwComponentlibrary\Utility\Scanner;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

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
     * Discover and print all styleguide components
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
}
