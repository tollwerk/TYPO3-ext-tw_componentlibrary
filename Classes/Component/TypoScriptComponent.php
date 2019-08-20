<?php

/**
 * TypoScript component
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

use Exception;
use Tollwerk\TwComponentlibrary\Utility\TypoScriptUtility;
use TYPO3\CMS\Core\Error\Http\ServiceUnavailableException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;

/**
 * Abstract TypoScript component
 *
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 */
abstract class TypoScriptComponent extends AbstractComponent
{
    /**
     * Component type
     *
     * @var string
     */
    protected $type = self::TYPE_TYPOSCRIPT;
    /**
     * TypoScript files
     *
     * @var string[]
     */
    protected $typoScriptFiles = [];

    /**
     * Render this component
     *
     * @return string Rendered component (HTML)
     * @throws ServiceUnavailableException
     * @throws InvalidArgumentNameException
     * @throws InvalidExtensionNameException
     */
    public function render(): string
    {
        // Set the request arguments as GET parameters
        $_GET = $this->getRequestArguments();

        // Instantiate a frontend controller
        $typoScript = TypoScriptUtility::extractTypoScriptKeyForPidAndType(
            $this->page,
            $this->typeNum,
            $this->config
        );
        try {
            $this->controllerContext->getRequest()->setOriginalRequestMappingResults($this->validationErrors);
            $result = $this->beautify(call_user_func_array([$GLOBALS['TSFE']->cObj, 'cObjGetSingle'], $typoScript));

            // In case of an error
        } catch (Exception $e) {
            $result = '<pre class="error"><strong>'.$e->getMessage().'</strong>'.PHP_EOL
                      .$e->getTraceAsString().'</pre>';
        }

        return $result;
    }

    /**
     * Set the TypoScript key
     *
     * @param string $key TypoScript key
     */
    protected function setTypoScriptKey($key)
    {
        $this->config = trim($key) ?: null;
    }


    /**
     * Add a TypoScript file
     *
     * @param string $typoScriptFile TypoScript file
     */
    protected function addTypoScriptFile(string $typoScriptFile)
    {
        $typoScriptFile = trim($typoScriptFile);
        if (strlen($typoScriptFile)) {
            $this->typoScriptFiles[] = $typoScriptFile;
        }
    }

    /**
     * Return component specific properties
     *
     * @return array Component specific properties
     * @throws ServiceUnavailableException
     */
    protected function exportInternal()
    {
        // Read the linked TypoScript
        if ($this->config !== null) {
            $typoScript     = TypoScriptUtility::extractTypoScriptKeyForPidAndType(
                $this->page,
                $this->typeNum,
                $this->config
            );
            $this->template = empty($typoScript) ?
                null : TypoScriptUtility::serialize(implode('.', explode('.', $this->config, -1)), $typoScript);
            $this->config   = ['typoScriptKey' => $this->config, 'typoScriptFiles' => $this->typoScriptFiles];
        }

        return parent::exportInternal();
    }


    /**
     * Return all component resources
     *
     * @return string[] Component resource files
     */
    public function getResources(): array
    {
        $typoScriptFiles = parent::getResources();

        return array_merge($typoScriptFiles, $this->typoScriptFiles);
    }
}
