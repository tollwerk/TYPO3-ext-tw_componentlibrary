<?php

/**
 * Component Kickstarter
 *
 * @category Tollwerk
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Utility
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

namespace Tollwerk\TwComponentlibrary\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\CommandException;

/**
 * Component Kickstarter
 *
 * `typo3/cli_dispatch.phpsh extbase component:create --name Component --type fluid|typoscript|extbase --extension my_extension`
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Utility
 */
class Kickstarter
{
    /**
     * Kickstart a new component
     *
     * @param string $name Component name
     * @param string $type Component type
     * @param string $extension Host extension
     * @throws CommandException If the host extension is invalid
     */
    public static function create($name, $type, $extension, $vendor)
    {
        // Prepare the component name
        $componentLabel = $componentName = array_pop($name);
        if (substr($componentName, -9) !== 'Component') {
            $componentName .= 'Component';
        }

        // Prepare the component directory
        $componentPath = implode(DIRECTORY_SEPARATOR, $name);
        $componentAbsPath = GeneralUtility::getFileAbsFileName(
            'EXT:'.$extension.DIRECTORY_SEPARATOR.'Components'.DIRECTORY_SEPARATOR
            .implode(DIRECTORY_SEPARATOR, $name)
        );
        if (!is_dir($componentAbsPath) && !mkdir($componentAbsPath, 0775, true)) {
            throw new CommandException('Could not create component directory', 1507997978);
        }

        // Prepare the component namespace
        $componentNamespace = $vendor.'\\Component\\'.implode('\\', $name);

        // Copy the skeleton template
        $substitute = [
            '###extension###' => $extension,
            '###namespace###' => $componentNamespace,
            '###label###' => $componentLabel,
            '###tspath###' => strtolower(implode('.', array_merge($name, [$componentLabel]))),
            '###path###' => $componentPath,
        ];
        $skeletonTemplate = GeneralUtility::getFileAbsFileName('EXT:tw_componentlibrary/Resources/Private/Skeleton/'.ucfirst($type).'.php');
        $skeletonString = strtr(file_get_contents($skeletonTemplate), $substitute);
        file_put_contents($componentAbsPath.DIRECTORY_SEPARATOR.$componentName.'.php', $skeletonString);
    }
}
