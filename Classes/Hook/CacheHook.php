<?php

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

namespace Tollwerk\TwComponentlibrary\Hook;

use TYPO3\CMS\Backend\Toolbar\ClearCacheActionsHookInterface;

/**
 * Hook for extending the backend cache menu
 *
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Controller
 */
class CacheHook implements ClearCacheActionsHookInterface
{
    /**
     * Add an entry to the CacheMenuItems array
     *
     * @param array $cacheActions Array of CacheMenuItems
     * @param array $optionValues Array of AccessConfigurations-identifiers (typically  used by userTS with
     *                            options.clearCache.identifier)
     */
    public function manipulateCacheActions(&$cacheActions, &$optionValues)
    {
        $extensionConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extParams']['tw_componentlibrary'];
        if ($GLOBALS['BE_USER']->isAdmin() && !empty($extensionConfiguration['componentlibrary'])) {
            $componentLibrary = $extensionConfiguration['componentlibrary'];
            $cacheActions[]   = array(
                'id'             => $componentLibrary,
                'title'          => 'LLL:EXT:tw_componentlibrary/Resources/Private/Language/locallang_core.xlf:cache.'.$componentLibrary.'.title',
                'description'    => 'LLL:EXT:tw_componentlibrary/Resources/Private/Language/locallang_core.xlf:cache.'.$componentLibrary.'.description',
                'href'           => '/typo3/index.php?route=%2F'.$componentLibrary,
                'iconIdentifier' => 'tx_twcomponentlibrary_cache'
            );
        }
    }
}
