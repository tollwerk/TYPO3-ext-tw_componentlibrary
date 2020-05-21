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

use Exception;
use RuntimeException;
use Tollwerk\TwBase\Utility\FrontendUriUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Error\Http\ServiceUnavailableException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\TypoScript\TemplateService;
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
     * Cached TypoScript Controller instances by root page
     *
     * @var TypoScriptFrontendController[]
     */
    protected static $rootPageTyposcriptController = [];

    /**
     * Extract and return a TypoScript key for a particular page and type
     *
     * @param int $id      Page ID
     * @param int $typeNum Page type
     * @param string $key  TypoScript key
     *
     * @return array TypoScript values
     * @throws ServiceUnavailableException
     * @throws Exception If the TypoScript key is invalid
     */
    public static function extractTypoScriptKeyForPidAndType($id, $typeNum, $key)
    {
        $key = trim($key);
        if (!strlen($key)) {
            throw new RuntimeException(sprintf('Invalid TypoScript key "%s"', $key), 1481365294);
        }

        // Get a frontend controller for the page id and type
        if (TYPO3_MODE != 'FE') {
            $TSFE       = self::getTypoScriptFrontendController($id, $typeNum);
            $TSFE->tmpl = GeneralUtility::makeInstance(TemplateService::class, $TSFE->getContext(), null, $TSFE);
            $TSFE->tmpl->start(BackendUtility::BEgetRootLine($id));
        } else {
            $TSFE =& $GLOBALS['TSFE'];
        }
        list($name, $conf) = GeneralUtility::makeInstance(TypoScriptParser::class)->getVal($key, $TSFE->tmpl->setup);
        $lastKey = explode('.', $key);
        $lastKey = array_pop($lastKey);

        return [$lastKey => $name, $lastKey.'.' => $conf];
    }

    /**
     * Create a TypoScript frontend controller for a particular page ID
     *
     * @param int $pageUid     Page ID
     * @param int $pageType    Page type
     * @param string $language Language
     *
     * @return TypoScriptFrontendController TypoScript frontend controller
     * @throws ServiceUnavailableException
     * @throws SiteNotFoundException
     */
    public static function getTypoScriptFrontendController(
        int $pageUid,
        int $pageType = 0,
        string $language = null
    ): TypoScriptFrontendController {
        // Initialize the time tracker if necessary
        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = new TimeTracker();
            $GLOBALS['TT']->start();
        }

        // Find the root page for the requested page ID
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $site       = $siteFinder->getSiteByPageId($pageUid);
        if ($site instanceof Site) {
            $rootPage = $site->getRootPageId();

            // If there's no TypoScript frontend controller for the root page yet
            if (empty(self::$rootPageTyposcriptController[$rootPage.'/'.$pageType])) {
                $backupTsfeController = $GLOBALS['TSFE'] ?? null;

                self::$rootPageTyposcriptController[$rootPage.'/'.$pageType] = version_compare(TYPO3_version, '10.0.0',
                    '>=') ?
                    self::create10xContext($site) :
                    self::createUpTo9xContext(
                        $site,
                        $pageUid,
                        $pageType
                    );

                // Restore backed-up TSFE
                if ($backupTsfeController) {
                    $GLOBALS['TSFE'] = $backupTsfeController;
                }
            }

            return self::$rootPageTyposcriptController[$rootPage.'/'.$pageType];
        }

        throw new RuntimeException('Can\'t find site for page ID '.$pageUid, 1563878859);
    }

    /**
     * Create a TYPO3 >= 10.x frontend engine
     *
     * @param Site $site
     *
     * @return TypoScriptFrontendController
     */
    protected static function create10xContext(Site $site): TypoScriptFrontendController
    {
        $GLOBALS['TSFE']           = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            GeneralUtility::makeInstance(Context::class),
            $site,
            $site->getDefaultLanguage()
        );
        $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Domain\Repository\PageRepository::class);
        $GLOBALS['TSFE']->newCObj();

        return $GLOBALS['TSFE'];
    }

    /**
     * Create a TYPO3 <10.x frontend engine
     *
     * @param Site $site    Site
     * @param int $pageUid  Page ID
     * @param int $pageType Page Type
     *
     * @return TypoScriptFrontendController
     * @throws ServiceUnavailableException
     */
    protected static function createUpTo9xContext(Site $site, int $pageUid, int $pageType): TypoScriptFrontendController
    {
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            (array)$GLOBALS['TYPO3_CONF_VARS'],
            $pageUid,
            $pageType
        );

        $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance(PageRepository::class);
        $GLOBALS['TSFE']->connectToDB();
        $GLOBALS['TSFE']->initFEuser();
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->rootLine = $GLOBALS['TSFE']->sys_page->getRootLine($pageUid, '');

        try {
            $GLOBALS['TSFE']->getConfigArray();
        } catch (ServiceUnavailableException $e) {
            // Skip unconfigured page type
        }

        // Calculate the absolute path prefix
        if (!empty($GLOBALS['TSFE']->config['config']['absRefPrefix'])) {
            $absRefPrefix                  = trim($GLOBALS['TSFE']->config['config']['absRefPrefix']);
            $GLOBALS['TSFE']->absRefPrefix = ($absRefPrefix === 'auto') ?
                $site->getAttribute('base') : $absRefPrefix;
        } else {
            $GLOBALS['TSFE']->absRefPrefix = '';
        }

        // Initialize a content object
        $GLOBALS['TSFE']->newCObj();

        return $GLOBALS['TSFE'];
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
