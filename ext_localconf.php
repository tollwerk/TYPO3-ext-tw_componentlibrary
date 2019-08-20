<?php

/**
 * Local configuration
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwComponentlibrary
 * @author    Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license   http://opensource.org/licenses/MIT The MIT License (MIT)
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

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

call_user_func(
    function() {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'TwComponentlibrary',
            'Component',
            [\Tollwerk\TwComponentlibrary\Controller\ComponentController::class => 'render'],
            [\Tollwerk\TwComponentlibrary\Controller\ComponentController::class => 'renders']
        );

        // Override the default Extbase template view
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Fluid\View\TemplateView::class] = array(
            'className' => \Tollwerk\TwComponentlibrary\Component\TemplateView::class,
        );

        // Exclude the component GET parameter from cHash calculation
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_twcomponentlibrary_component[component]';

        // Component library integration
        if (
            !empty($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['tw_componentlibrary']['componentlibrary'])
            && !empty($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['tw_componentlibrary']['script'])
            && file_exists($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['tw_componentlibrary']['script'])
        ) {
            // Register icon
            $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
            $iconRegistry->registerIcon(
                'tx_twcomponentlibrary_cache',
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                [
                    'source' => 'EXT:tw_componentlibrary/Resources/Public/Icons/'.ucfirst(
                            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['tw_componentlibrary']['componentlibrary']
                        ).'.svg'
                ]
            );

            // Extend the backend cache action menu
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['additionalBackendItems']['cacheActions'][] = \Tollwerk\TwComponentlibrary\Hook\CacheHook::class;
        }

        // Graph service
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
            'tw_componentlibrary',
            // Service type
            'graphviz',
            // Service key
            'tx_twcomponentlibrary_graphviz',
            array(
                'title'       => 'GraphViz',
                'description' => 'Create an SVG graph uzing the GraphViz library',
                'subtype'     => 'svg',
                'available'   => true,
                'priority'    => 60,
                'quality'     => 80,
                'os'          => '',
                'exec'        => 'ccomps,dot,gvpack,neato',
                'className'   => \Tollwerk\TwComponentlibrary\Service\GraphvizService::class
            )
        );
    }
);
