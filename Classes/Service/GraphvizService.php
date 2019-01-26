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

namespace Tollwerk\TwComponentlibrary\Service;

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
     * @param string $graph GraphViz graph
     * @return string SVG component graph
     * @see https://stackoverflow.com/questions/8002352/how-to-control-subgraphs-layout-in-dot
     */
    public function createGraph($graph)
    {
        // Write the dot source to a temporary file
        $tempDot = tempnam(sys_get_temp_dir(), 'DOT_');
        $this->registerTempFile($tempDot);
        file_put_contents($tempDot, $graph);

        // Create component graph
//        $dotCommand = 'ccomps -x '.CommandUtility::escapeShellArgument($tempDot).' | dot -Nfontname=sans-serif -Efontname=sans-serif | gvpack -array_1 | neato -Tsvg ';
        $dotCommand = 'dot -Tsvg -Nfontname=sans-serif -Efontname=sans-serif '.CommandUtility::escapeShellArgument($tempDot);
        $output = $returnValue = null;
        CommandUtility::exec($dotCommand, $output, $returnValue);
        return $returnValue ? '' : implode('', (array)$output);
    }
}
