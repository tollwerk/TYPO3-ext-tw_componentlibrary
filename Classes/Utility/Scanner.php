<?php

/**
 * Component scanner
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Utility
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

namespace Tollwerk\TwComponentlibrary\Utility;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use ReflectionException;
use RegexIterator;
use Tollwerk\TwComponentlibrary\Component\ComponentInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use function json_decode;

/**
 * Component scanner
 *
 * `typo3/cli_dispatch.phpsh extbase component:discover`
 *
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Utility
 */
class Scanner
{
    /**
     * Local configuration extensions
     *
     * @var array
     */
    protected static $localConfigurations = [];

    /**
     * Discover all components or component resources
     *
     * @param bool $resources Return the component resources only
     * @param bool $dev       Include development components
     *
     * @return array Components / Component resources
     * @throws ReflectionException
     */
    public static function discoverAll(bool $resources = false, bool $dev = false): array
    {
        $components = [];

        // Run through all extensions
        foreach (ExtensionManagementUtility::getLoadedExtensionListArray() as $extensionKey) {
            $components = array_merge($components, self::discoverExtensionComponents($extensionKey, $resources, $dev));
        }

        return $components;
    }

    /**
     * Discover the components of a single extension
     *
     * @param string $extensionKey Extension key
     * @param bool $resources      Return the component resources only
     * @param bool $dev            Include development components
     *
     * @return array Extension components
     * @throws ReflectionException
     */
    protected static function discoverExtensionComponents(string $extensionKey, bool $resources, bool $dev): array
    {
        // Test if the extension contains a component directory
        $extCompRootDirectory = ExtensionManagementUtility::extPath($extensionKey, 'Components');

        return is_dir($extCompRootDirectory) ?
            self::discoverExtensionComponentDirectory($extCompRootDirectory, $resources, $dev) : [];
    }

    /**
     * Recursively scan a directory for components and return a component list
     *
     * @param string $directory Directory path
     * @param bool $resources   Return the component resources only
     * @param bool $dev         Include development components
     *
     * @return array Components
     * @throws ReflectionException
     */
    protected static function discoverExtensionComponentDirectory(string $directory, bool $resources, bool $dev): array
    {
        $components        = [];
        $directoryIterator = new RecursiveDirectoryIterator($directory);
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator);
        $regexIterator     = new RegexIterator(
            $recursiveIterator,
            PATH_SEPARATOR.'^'.preg_quote($directory.DIRECTORY_SEPARATOR).'.+Component\.php$'.PATH_SEPARATOR,
            RecursiveRegexIterator::GET_MATCH
        );

        // Run through all potential component files
        foreach ($regexIterator as $component) {
            // Run through all classes declared in the file
            foreach (self::discoverClassesInFile(file_get_contents($component[0])) as $className) {
                // Test if this is a component class
                $classReflection = new ReflectionClass($className);
                if ($classReflection->implementsInterface(ComponentInterface::class)) {
                    if ($dev || !$classReflection->getConstant('DEVELOPMENT')) {
                        if ($resources) {
                            $components[$className] = array_map(
                                [GeneralUtility::class, 'getFileAbsFileName'],
                                self::discoverComponent($className, true)
                            );
                            continue;
                        }
                        $components[] = self::addLocalConfiguration(
                            $component[0],
                            self::discoverComponent($className, false)
                        );
                    }
                }
            }
        }

        return $components;
    }

    /**
     * Discover the classes declared in a file
     *
     * @param string $phpCode PHP code
     *
     * @return array Class names
     */
    protected static function discoverClassesInFile($phpCode)
    {
        $classes          = array();
        $tokens           = token_get_all($phpCode);
        $gettingClassname = $gettingNamespace = false;
        $namespace        = '';
        $lastToken        = null;

        // Run through all tokens
        for ($t = 0, $tokenCount = count($tokens); $t < $tokenCount; ++$t) {
            $token = $tokens[$t];

            // If this is a namespace token
            if (is_array($token) && ($token[0] == T_NAMESPACE)) {
                $namespace        = '';
                $gettingNamespace = true;
                continue;
            }

            // If this is a class name token
            if (is_array($token) && ($token[0] == T_CLASS) && (!is_array(
                        $lastToken
                    ) || ($lastToken[0] !== T_PAAMAYIM_NEKUDOTAYIM))) {
                $gettingClassname = true;
            }

            // If we're getting a namespace
            if ($gettingNamespace === true) {
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
                    $namespace .= $token[1];
                } elseif ($token === ';') {
                    $gettingNamespace = false;
                }

                // Else if we're getting the class name
            } elseif ($gettingClassname === true) {
                if (is_array($token) && ($token[0] == T_STRING)) {
                    $classes[]        = ($namespace ? $namespace.'\\' : '').$token[1];
                    $gettingClassname = false;
                }
            }

            $lastToken = $token;
        }

        return $classes;
    }

    /**
     * Amend the directory specific local configuration
     *
     * @param string $componentDirectory Component directory
     * @param array $component           Component
     *
     * @return array Amended component
     */
    protected static function addLocalConfiguration($componentDirectory, array $component)
    {
        $component['local']   = [];
        $componentDirectories = [];
        for ($dir = 0; $dir < count($component['path']); ++$dir) {
            $componentDirectory = $componentDirectories[] = dirname($componentDirectory);
        }

        foreach (array_reverse($componentDirectories) as $componentDirectory) {
            $component['local'][] = self::getLocalConfiguration($componentDirectory);
        }

        return $component;
    }

    /**
     * Read, cache and return a directory specific local configuration
     *
     * @param string $dirname Directory name
     *
     * @return array Directory specific local configuration
     */
    protected static function getLocalConfiguration($dirname)
    {
        if (is_dir($dirname) && empty(self::$localConfigurations[$dirname])) {
            $localConfig                         = $dirname.DIRECTORY_SEPARATOR.'local.json';
            self::$localConfigurations[$dirname] = file_exists($localConfig) ?
                (array)@json_decode(file_get_contents($localConfig)) : [];
        }

        return self::$localConfigurations[$dirname];
    }

    /**
     * Discover a single component class
     *
     * @param string $componentClass Component class
     * @param bool $resources        Return the component resources only
     *
     * @return array Component profile / resources
     */
    public static function discoverComponent(string $componentClass, bool $resources): array
    {
        /** @var ComponentInterface $component */
        $component = new $componentClass;

        return $resources ? $component->getResources() : $component->export();
    }
}
