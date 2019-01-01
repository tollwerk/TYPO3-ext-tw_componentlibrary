<?php

/**
 * Icon utility
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Utility
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2018 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  Copyright © 2018 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

/**
 * Icon Utility
 *
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Utility
 */
class IconUtility
{
    /**
     * Return all icons as TCA select items
     *
     * @param int $id      Page ID
     * @param int $typeNum Page type
     *
     * @return array[] Icon TCA select items
     */
    public static function getIconTcaSelectItems(int $id = 1, int $typeNum = 0): array
    {
        $icons = [['---', '']];
        foreach (self::getIcons($id, $typeNum) as $iconBasename => $iconName) {
            $icons[] = [$iconName, $iconBasename];
        }

        return $icons;
    }

    /**
     * Return all icon assets
     *
     * @param int $id      Page ID
     * @param int $typeNum Page type
     *
     * @return string[] Icon assets
     */
    public static function getIcons(int $id = 1, int $typeNum = 0): array
    {
        $icons           = [];
        $typoScriptKey   = 'plugin.tx_twcomponentlibrary.settings.iconDirectories';
        $iconDirectories = TypoScriptUtility::extractTypoScriptKeyForPidAndType($id, $typeNum, $typoScriptKey);
        $iconDirectories = GeneralUtility::trimExplode(',', $iconDirectories['iconDirectories'], true);
        foreach ($iconDirectories as $iconDirectory) {
            $iconsBaseDirectory = GeneralUtility::getFileAbsFileName($iconDirectory);

            foreach (glob($iconsBaseDirectory.DIRECTORY_SEPARATOR.'*.svg') as $iconFile) {
                $iconBasename         = basename($iconFile);
                $iconName             = trim(preg_replace('/([A-Z])/', " $1",
                    pathinfo($iconBasename, PATHINFO_FILENAME)));
                $icons[$iconBasename] = $iconName;
            }
        }
        asort($icons);

        return $icons;
    }
}