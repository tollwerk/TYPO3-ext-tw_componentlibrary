<?php

/**
 * Component creation CLI command
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
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tollwerk\TwComponentlibrary\Component\ComponentInterface;
use Tollwerk\TwComponentlibrary\Utility\Kickstarter;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Component creation CLI command
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Command
 * @cli
 */
class CreateCommand extends Command
{
    /**
     * Configure the command
     */
    public function configure()
    {
        $this->setDescription('Scaffolds a new component by creating a stub component description.');
        $this->setHelp('Creates a new component.'.LF.'If you want to get more detailed information, use the --verbose option.');

        $this->addArgument('name', InputArgument::REQUIRED, 'Component path and name');
        $this->addArgument('type', InputArgument::REQUIRED, 'Component type');
        $this->addArgument('extension', InputArgument::OPTIONAL, 'Host extension');
        $this->addArgument('vendor', InputArgument::OPTIONAL, 'Host extension vendor name');
    }

    /**
     * Creates a new component
     *
     * @param InputInterface $input Input
     * @param OutputInterface $output Output
     * @return int Success / exit code
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // Prepare the component name
        $name = GeneralUtility::trimExplode('/', $input->getArgument('name'), true);
        if (!count($name)) {
            throw new InvalidArgumentException('Empty / invalid component name', 1507996606);
        }

        // Prepare the component type
        $type = strtolower($input->getArgument('type'));
        if (!in_array($type, ComponentInterface::TYPES)) {
            throw new InvalidArgumentException(sprintf('Invalid component type "%s"', $type), 1507996917);
        }

        // Prepare the provider extension name
        $extension = trim($input->getArgument('extension') ?: $GLOBALS['TYPO3_CONF_VARS']['EXT']['extParams']['tw_componentlibrary']['defaultextension']);
        if (!strlen($extension) || !ExtensionManagementUtility::isLoaded($extension)) {
            throw new InvalidArgumentException(sprintf('Invalid provider extension "%s"', $extension), 1507997408);
        }

        // Prepare the provider extension vendor name
        $vendor = trim($input->getArgument('vendor') ?: $GLOBALS['TYPO3_CONF_VARS']['EXT']['extParams']['tw_componentlibrary']['defaultvendor']);
        if (!strlen($vendor)) {
            throw new InvalidArgumentException(sprintf('Invalid provider extension vendor name "%s"', $vendor),
                1507998569);
        }

        try {
            return intval(!Kickstarter::create($name, $type, $extension, $vendor));
        } catch (\RuntimeException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }
    }
}
