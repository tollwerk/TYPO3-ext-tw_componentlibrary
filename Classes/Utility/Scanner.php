<?php

/**
 * Component scanner
 *
 * @category Tollwerk
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Classes
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

namespace Tollwerk\TwComponentlibrary\Utility;

use Tollwerk\TwComponentlibrary\Component\AbstractComponent;
use Tollwerk\TwComponentlibrary\Component\ComponentInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Component scanner
 *
 * `typo3/cli_dispatch.phpsh extbase component:discover`
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Classes
 */
class Scanner
{
    /**
     * Discover all components
     *
     * @return array Components
     */
    public static function discover()
    {
        $components = [];

        // Run through all extensions
        foreach (ExtensionManagementUtility::getLoadedExtensionListArray() as $extensionKey) {
            $components = array_merge($components, self::discoverExtensionComponents($extensionKey));
        }

        return $components;
    }

    /**
     * Discover the components of a single extension
     *
     * @param $extensionKey
     */
    protected static function discoverExtensionComponents($extensionKey)
    {
        // Test if the extension contains a component directory
        $extCompRootDirectory = ExtensionManagementUtility::extPath($extensionKey, 'Components');
        return is_dir($extCompRootDirectory) ? self::discoverExtensionComponentDirectory($extCompRootDirectory) : [];
    }

    /**
     * Recursively scan a directory for components and return a component list
     *
     * @param string $directory Directory path
     * @return array Components
     */
    protected static function discoverExtensionComponentDirectory($directory)
    {
        $components = [];
        $directoryIterator = new \RecursiveDirectoryIterator($directory);
        $recursiveIterator = new \RecursiveIteratorIterator($directoryIterator);
        $regexIterator = new \RegexIterator(
            $recursiveIterator,
            PATH_SEPARATOR.'^'.preg_quote($directory.DIRECTORY_SEPARATOR).'(.+)Component\.php$'.PATH_SEPARATOR,
            \RecursiveRegexIterator::GET_MATCH
        );

        // Run through all potential component files
        foreach ($regexIterator as $component) {
            // Run through all classes declared in the file
            foreach (self::discoverClassesInFile(file_get_contents($component[0])) as $className) {
                // Test if this is a component class
                $classReflection = new \ReflectionClass($className);
                if ($classReflection->implementsInterface(ComponentInterface::class)) {
                    $components[] = self::discoverComponentClass($className, $component[1]);
                }
            }
        }

        return $components;
    }

    /**
     * Discover a single component class
     *
     * @param string $componentClass Component class
     * @param string $componentPath Component path
     * @return array Component profile
     */
    protected static function discoverComponentClass($componentClass, $componentPath)
    {
        /** @var ComponentInterface $component */
        $component = new $componentClass;
        return array_merge($component->export(), [
            'path' => array_map([AbstractComponent::class, 'expandComponentName'],
                explode(DIRECTORY_SEPARATOR, dirname($componentPath))),
        ]);
    }

    /**
     * Discover the classes declared in a file
     *
     * @param string $phpCode PHP code
     * @return array Class names
     */
    protected static function discoverClassesInFile($phpCode)
    {
        $classes = array();
        $tokens = token_get_all($phpCode);
        $gettingClassname = $gettingNamespace = false;
        $namespace = '';

        // Run through all tokens
        for ($t = 0, $tokenCount = count($tokens); $t < $tokenCount; ++$t) {
            $token = $tokens[$t];

            // If this is a namespace token
            if (is_array($token) && ($token[0] == T_NAMESPACE)) {
                $namespace = '';
                $gettingNamespace = true;
                continue;
            }

            // If this is a class name token
            if (is_array($token) && ($token[0] == T_CLASS)) {
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
                    $classes[] = ($namespace ? $namespace.'\\' : '').$token[1];
                    $gettingClassname = false;
                }
            }
        }
        return $classes;
    }
}
