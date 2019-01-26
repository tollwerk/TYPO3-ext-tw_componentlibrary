<?php

/**
 * data
 *
 * @category    Tollwerk
 * @package     Tollwerk\TwComponentlibrary
 * @subpackage  Tollwerk\TwComponentlibrary\Utility
 * @author      Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright   Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license     http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2019 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of
 *  this software and associated documentation files (the "Software"), to deal in
 *  the Software without restriction, including without limitation the rights to
 *  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 *  the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ***********************************************************************************/

namespace Tollwerk\TwComponentlibrary\Utility;

use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * TypoScript extractor
 *
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Utility
 */
class TypoScriptUtility
{
    /**
     * Cached frontend controllers
     *
     * @var array
     */
    protected static $frontendControllers = [];

    /**
     * Extract and return a TypoScript key for a particular page and type
     *
     * @param int $id      Page ID
     * @param int $typeNum Page type
     * @param string $key  TypoScript key
     *
     * @return array TypoScript values
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     * @throws \Exception If the TypoScript key is invalid
     */
    public static function extractTypoScriptKeyForPidAndType($id, $typeNum, $key)
    {
        $key = trim($key);
        if (!strlen($key)) {
            throw new \RuntimeException(sprintf('Invalid TypoScript key "%s"', $key), 1481365294);
        }

        // Get a frontend controller for the page id and type
        $TSFE = self::getTSFE($id, $typeNum);
        list($name, $conf) = GeneralUtility::makeInstance(TypoScriptParser::class)->getVal($key, $TSFE->tmpl->setup);
        $lastKey = explode('.', $key);
        $lastKey = array_pop($lastKey);

        return [$lastKey => $name, $lastKey.'.' => $conf];
    }

    /**
     * Instantiate a Frontend controller for the given configuration
     *
     * @param int $id      Page ID
     * @param int $typeNum Page Type
     *
     * @return TypoScriptFrontendController Frontend controller
     * @throws \Exception
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     */
    public static function getTSFE($id, $typeNum)
    {
        // Initialize the tracker if necessary
        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = new TimeTracker(false);
            $GLOBALS['TT']->start();
        }

        if (!array_key_exists("$id/$typeNum", self::$frontendControllers)) {
            $tsfeBackup      = empty($GLOBALS['TSFE']) ? null : $GLOBALS['TSFE'];
            $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $GLOBALS['TYPO3_CONF_VARS'],
                $id,
                $typeNum
            );

            $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance(PageRepository::class);
            $GLOBALS['TSFE']->sys_page->init(true);
            $GLOBALS['TSFE']->connectToDB();
            $GLOBALS['TSFE']->initFEuser();
            $GLOBALS['TSFE']->determineId();
            $GLOBALS['TSFE']->initTemplate();
            $GLOBALS['TSFE']->rootLine = $GLOBALS['TSFE']->sys_page->getRootLine($id, '');
            $GLOBALS['TSFE']->getConfigArray();

            // Calculate the absolute path prefix
            if (!empty($GLOBALS['TSFE']->config['config']['absRefPrefix'])) {
                $absRefPrefix                  = trim($GLOBALS['TSFE']->config['config']['absRefPrefix']);
                $GLOBALS['TSFE']->absRefPrefix = ($absRefPrefix === 'auto') ? GeneralUtility::getIndpEnv(
                    'TYPO3_SITE_PATH'
                ) : $absRefPrefix;
            } else {
                $GLOBALS['TSFE']->absRefPrefix = '';
            }

            self::$frontendControllers["$id/$typeNum"] = $GLOBALS['TSFE'];

            if ($tsfeBackup) {
                $GLOBALS['TSFE'] = $tsfeBackup;
            } else {
                unset($GLOBALS['TSFE']);
            }
        }

        return self::$frontendControllers["$id/$typeNum"];
    }

    /**
     * Serialize a TypoScript fragment
     *
     * @param string $prefix    Key prefix
     * @param array $typoscript TypoScript fragment
     * @param int $indent       Indentation level
     *
     * @return string Serialized TypoScript
     */
    public static function serialize($prefix, array $typoscript, $indent = 0)
    {
        $serialized = [];

        // Sort the TypoScript fragment
        ksort($typoscript, SORT_NATURAL);

        // Run through the TypoScript fragment
        foreach ($typoscript as $key => $value) {
            $line = str_repeat(' ', $indent * 4);
            $line .= trim(strlen($prefix) ? "$prefix.$key" : $key, '.');

            // If the value is a list of values
            if (is_array($value)) {

                // If the list has only one element
                if (count($value) === 1) {
                    $line .= '.'.self::serialize('', $value, 0);
                } else {
                    $line .= ' {'.PHP_EOL.self::serialize('', $value, $indent + 1).PHP_EOL.'}';
                }

                // Else if it's a multiline value
            } elseif (preg_match('/\R/', $value)) {
                $line .= ' ('.PHP_EOL.$value.PHP_EOL.')';

                // Else: Simple assignment
            } else {
                $line .= ' = '.$value;
            }
            $serialized[] = $line;
        }

        return implode(PHP_EOL, $serialized);
    }
}
