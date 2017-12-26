<?php

/**
 * Component discovery CLI command
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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tollwerk\TwComponentlibrary\Component\Preview\BasicTemplate;
use Tollwerk\TwComponentlibrary\Utility\Scanner;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Component discovery CLI command
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Command
 * @cli
 */
class DiscoverCommand extends Command
{
    /**
     * Configure the command
     */
    public function configure()
    {
        $this->setDescription('Discovers the list of registered components and returns them as a JSON structure.');
        $this->setHelp('Discovers the list of registered components.'.LF.'If you want to get more detailed information, use the --verbose option.');
    }

    /**
     * Discovers all components
     *
     * @param InputInterface $input Input
     * @param OutputInterface $output Output
     * @return int Success / exit code
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $setup = $objectManager->get(BackendConfigurationManager::class)->getTypoScriptSetup();
        /** @var TypoScriptService $typoscriptService */
        $typoscriptService = $objectManager->get(TypoScriptService::class);
        $config = $typoscriptService->convertTypoScriptArrayToPlainArray($setup['plugin.']['tx_twcomponentlibrary.']);

        // Register common stylesheets & scripts
        BasicTemplate::addCommonStylesheets($config['settings']['stylesheets']);
        BasicTemplate::addCommonHeaderScripts($config['settings']['headerScripts']);
        BasicTemplate::addCommonFooterScripts($config['settings']['footerScripts']);

        $output->write(json_encode(Scanner::discoverAll(), JSON_PRETTY_PRINT));
        return 0;
    }
}
