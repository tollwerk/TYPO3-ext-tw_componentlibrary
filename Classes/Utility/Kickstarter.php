<?php

/**
 * Component Kickstarter
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

use RuntimeException as RuntimeExceptionAlias;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Component Kickstarter
 *
 * `vendor/bin/typo3 component:create --name Component --type fluid|typoscript|extbase|form --extension
 * my_extension`
 *
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Utility
 */
class Kickstarter
{
    /**
     * Kickstart a new component
     *
     * @param array $name       Component name
     * @param string $type      Component type
     * @param string $extension Host extension
     * @param string $vendor    Vendor name
     *
     * @return string Component class name
     * @throws RuntimeExceptionAlias If the component directory couldn't be created
     * @throws RuntimeExceptionAlias If the component descriptor couldn't be created
     */
    public static function create(array $name, string $type, string $extension, string $vendor): string
    {
        // Prepare the component name
        $componentLabel = $componentName = array_pop($name);
        if (substr($componentName, -9) !== 'Component') {
            $componentName .= 'Component';
        } else {
            $componentLabel = substr($componentLabel, 0, -9);
        }

        // Prepare the component directory
        $componentPath    = implode(DIRECTORY_SEPARATOR, $name);
        $componentAbsPath = GeneralUtility::getFileAbsFileName(
            'EXT:'.$extension.DIRECTORY_SEPARATOR.'Components'.DIRECTORY_SEPARATOR
            .implode(DIRECTORY_SEPARATOR, $name)
        );
        if (!is_dir($componentAbsPath) && !mkdir($componentAbsPath, 06775, true)) {
            throw new RuntimeExceptionAlias('Could not create component directory', 1507997978);
        }

        // Prepare the component namespace
        $componentNamespace = rtrim(
            $vendor.'\\'.GeneralUtility::underscoredToUpperCamelCase($extension)
            .'\\Component\\'.implode('\\', $name),
            '\\'
        );

        // Copy the skeleton template
        $substitute       = [
            '###extension###' => $extension,
            '###namespace###' => $componentNamespace,
            '###label###'     => $componentLabel,
            '###tspath###'    => strtolower(implode('.', array_merge($name, [$componentLabel]))),
            '###path###'      => $componentPath,
        ];
        $skeletonTemplate = GeneralUtility::getFileAbsFileName(
            'EXT:tw_componentlibrary/Resources/Private/Skeleton/'.ucfirst($type).'.php'
        );
        $skeletonString   = strtr(file_get_contents($skeletonTemplate), $substitute);
        $skeletonFile     = $componentAbsPath.DIRECTORY_SEPARATOR.$componentName.'.php';

        // Create the component descriptor
        if (!file_put_contents($skeletonFile, $skeletonString) || !chmod($skeletonFile, 0664)) {
            throw new RuntimeExceptionAlias('Could not create component descriptor', 1566130574);
        }

        return $componentNamespace.'\\'.$componentLabel;
    }
}
