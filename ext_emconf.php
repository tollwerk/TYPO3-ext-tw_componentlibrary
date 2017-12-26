<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "tw_componentlibrary"
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'tollwerk TYPO3 Component Library',
    'description' => 'TYPO3 Component Library',
    'category' => 'misc',
    'author' => 'Joschi Kuphal',
    'author_email' => 'joschi@tollwerk.de',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.4.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '9.0.0-9.99.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
