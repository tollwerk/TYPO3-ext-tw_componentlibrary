<?php

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

namespace Tollwerk\TwComponentlibrary\Service;

use Tollwerk\TwComponentlibrary\Utility\Scanner;
use TYPO3\CMS\Core\Service\AbstractService;
use TYPO3\CMS\Core\Utility\CommandUtility;

/**
 * Graphviz Service
 */
class GraphvizService extends AbstractService
{
    /**
     * Create an SVG component graph
     *
     * @param string $rootComponent Optional: Root component
     * @return string SVG component graph
     */
    public function createComponentGraph($rootComponent = null)
    {
        // Write the dot source to a temporary file
        $tempDot = tempnam(sys_get_temp_dir(), 'DOT_');
        $this->registerTempFile($tempDot);
        file_put_contents($tempDot, $this->createDotGraph($rootComponent));

        // Create component graph
        $dotCommand = 'dot -Tsvg '.CommandUtility::escapeShellArgument($tempDot);
        $output = $returnValue = null;
        CommandUtility::exec($dotCommand, $output, $returnValue);
        return $returnValue ? '' : implode('', (array)$output);
    }

    /**
     * Create a dot graph for a set of components
     *
     * @param string $rootComponent Optional: Root component
     * @return string SVG component graph
     */
    protected function createDotGraph($rootComponent = null)
    {
        $components = $rootComponent ? [Scanner::discoverComponent($rootComponent)] : Scanner::discoverAll();

        return '';
    }
}
