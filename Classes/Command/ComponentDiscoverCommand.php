<?php

/**
 * Component command line controller
 *
 * @category    Tollwerk
 * @package     Tollwerk\TwComponentlibrary
 * @subpackage  Tollwerk\TwComponentlibrary\Command
 * @author      Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright   Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license     http://opensource.org/licenses/MIT The MIT License (MIT)
 */

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

namespace Tollwerk\TwComponentlibrary\Command;

use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tollwerk\TwComponentlibrary\Component\Preview\FluidTemplate;
use Tollwerk\TwComponentlibrary\Utility\Scanner;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Component discovery command
 *
 * @package    Tollwerk\TwComponentlibrary\Command
 * @subpackage Tollwerk\TwComponentlibrary\Command
 */
class ComponentDiscoverCommand extends Command
{
    /**
     * Extension configuration
     *
     * @var array
     */
    protected $config;

    /**
     * Constructor
     *
     * @param string|null $name
     *
     * @throws Exception
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $setup         = $objectManager->get(BackendConfigurationManager::class)->getTypoScriptSetup();
        $this->config  = $objectManager->get(TypoScriptService::class)
                                       ->convertTypoScriptArrayToPlainArray((array)$setup['plugin.']['tx_twcomponentlibrary.']);
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Discover all available components');
        $this->setHelp('Scan all installed extensions for component definitions and return a comprehensive JSON description');
    }

    /**
     * Executes the command for cleaning processed files
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws Exception
     * @throws ReflectionException
     * @throws InvalidConfigurationTypeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Register common stylesheets & scripts
        FluidTemplate::addCommonStylesheets($this->config['settings']['stylesheets']);
        FluidTemplate::addCommonHeaderScripts($this->config['settings']['headerScripts']);
        FluidTemplate::addCommonFooterScripts($this->config['settings']['footerScripts']);

        echo json_encode(Scanner::discoverAll(), JSON_PRETTY_PRINT);
    }
}
