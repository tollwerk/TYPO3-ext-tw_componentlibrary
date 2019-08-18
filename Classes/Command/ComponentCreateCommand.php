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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Tollwerk\TwComponentlibrary\Component\ComponentInterface;
use Tollwerk\TwComponentlibrary\Utility\Kickstarter;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\CommandException;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Component creation command
 *
 * @package    Tollwerk\TwComponentlibrary\Command
 * @subpackage Tollwerk\TwComponentlibrary\Command
 */
class ComponentCreateCommand extends Command
{
    /**
     * Component name
     *
     * @var array
     */
    protected $name;
    /**
     * Component type
     *
     * @var string
     */
    protected $type;
    /**
     * Component extension
     *
     * @var string
     */
    protected $extension;
    /**
     * Component vendor
     *
     * @var string
     */
    protected $vendor;

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Create a component');
        $this->setHelp('Create a new component and scaffold its descriptor file');
        $this->addArgument('name', InputArgument::OPTIONAL, 'Component name');
        $this->addArgument('type', InputArgument::OPTIONAL, 'Component type');
        $this->addArgument(
            'extension',
            InputArgument::OPTIONAL,
            'Component provider extension',
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['extParams']['tw_componentlibrary']['defaultextension']
        );
        $this->addArgument(
            'vendor',
            InputArgument::OPTIONAL,
            'Component provider extension vendor',
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['extParams']['tw_componentlibrary']['defaultvendor']
        );
    }

    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @see InputInterface::bind()
     * @see InputInterface::validate()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Prepare the component name
        $this->name = trim($input->getArgument('name'));

        // Prepare the component type
        $this->type = trim($input->getArgument('type'));

        // Prepare the extension
        $this->extension = trim(
            $input->hasArgument('extension') ?
                $input->getArgument('extension') :
                $GLOBALS['TYPO3_CONF_VARS']['EXT']['extParams']['tw_componentlibrary']['defaultextension']
        );
        if (strlen($this->extension) && !ExtensionManagementUtility::isLoaded($this->extension)) {
            throw new InvalidArgumentException(
                sprintf('Invalid provider extension "%s"', $this->extension),
                1507997408
            );
        }

        // Prepare the vendor
        $this->vendor = trim(
            $input->hasArgument('vendor') ?
                $input->getArgument('vendor') :
                $GLOBALS['TYPO3_CONF_VARS']['EXT']['extParams']['tw_componentlibrary']['defaultvendor']
        );
    }

    /**
     * Interacts with the user.
     *
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        // Ask for the component name
        while (!strlen($this->name)) {
            $output->writeln('Please provide a component name');
            $helper     = $this->getHelper('question');
            $question   = new Question('Component name: ');
            $this->name = trim($helper->ask($input, $output, $question));
            $input->setArgument('name', $this->name);
        }
        $this->name = GeneralUtility::trimExplode('/', $this->name, true);
        if (!count($this->name)) {
            throw new InvalidArgumentException('Empty / invalid component name', 1507996606);
        }

        // Ask for a component type
        while (!strlen($this->type)) {
            $output->writeln('Please select a component type');
            $helper     = $this->getHelper('question');
            $question   = new ChoiceQuestion('Component type: ', ComponentInterface::TYPES);
            $this->type = trim($helper->ask($input, $output, $question));
            $input->setArgument('name', $this->type);
        }
        if (!in_array($this->type, ComponentInterface::TYPES)) {
            throw new InvalidArgumentException(sprintf('Invalid component type "%s"', $this->type), 1507996917);
        }

        // Ask for the extension
        while (!strlen($this->extension)) {
            $extensions    = [];
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $packageManger = $objectManager->get(PackageManager::class);
            foreach ($packageManger->getActivePackages() as $package) {
                if (!$package->isPartOfFactoryDefault() && (strpos($package->getPackagePath(),
                            'typo3conf') !== false)) {
                    $extensions[] = $package->getPackageKey();
                }
            }
            $output->writeln('Please select a provider extension');
            $helper          = $this->getHelper('question');
            $question        = new ChoiceQuestion('Extension: ', $extensions);
            $this->extension = $helper->ask($input, $output, $question);
            $input->setArgument('extension', $this->extension);
        }

        // Ask for a component type
        while (!strlen($this->vendor)) {
            $output->writeln('Please select a provider extension vendor');
            $helper       = $this->getHelper('question');
            $question     = new Question('Vendor name: ');
            $this->vendor = trim($helper->ask($input, $output, $question));
            $input->setArgument('name', $this->vendor);
        }
        if ($this->vendor != ucfirst($this->vendor)) {
            throw new InvalidArgumentException(
                sprintf('Invalid provider extension vendor name "%s"', $this->vendor),
                1507998569
            );
        }
    }

    /**
     * Executes the command for cleaning processed files
     *
     * @param InputInterface $input   Input
     * @param OutputInterface $output Output
     *
     * @throws CommandException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $component = Kickstarter::create($this->name, $this->type, $this->extension, $this->vendor);
            $output->writeln(sprintf('<fg=green>Successfully created component "%s"</>', $component));
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }
}
