<?php

/**
 * Content component
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
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

namespace Tollwerk\TwComponentlibrary\Component;

use Tollwerk\TwComponentlibrary\Utility\TypoScriptUtility;

/**
 * Abstract content component
 *
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 */
abstract class ContentComponent extends AbstractComponent
{
    /**
     * Component type
     *
     * @var string
     */
    protected $type = self::TYPE_CONTENT;

    /**
     * Render this component
     *
     * @return string Rendered component (HTML)
     */
    public function render()
    {
        // Set the request arguments as GET parameters
        $_GET = $this->getRequestArguments();

        try {
            $this->controllerContext->getRequest()->setOriginalRequestMappingResults($this->validationErrors);

            // Render the content element
            $result = $this->beautify($GLOBALS['TSFE']->cObj->cObjGetSingle('RECORDS', $this->config));

            // In case of an error
        } catch (\Exception $e) {
            $result = '<pre class="error"><strong>'.$e->getMessage().'</strong>'.PHP_EOL
                      .$e->getTraceAsString().'</pre>';
        }

        return $result;
    }

    /**
     * Set the content record UID
     *
     * @param int $id Content record UID
     */
    protected function setContentRecordId($id)
    {
        $this->config = intval($id) ? [
            'source'       => $id,
            'dontCheckPid' => 1,
            'tables'       => 'tt_content'
        ] : null;
    }

    /**
     * Return component specific properties
     *
     * @return array Component specific properties
     */
    protected function exportInternal()
    {
        // Read the TypoScript rendering configuration for the given content record
        if ($this->config !== null) {
            $this->template = '10 = RECORDS'.PHP_EOL.TypoScriptUtility::serialize('', [10 => $this->config]);
        }

        return parent::exportInternal();
    }
}
