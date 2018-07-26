<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'tw_componentlibrary',
    'Configuration/TypoScript',
    'TYPO3 Component Library'
);
