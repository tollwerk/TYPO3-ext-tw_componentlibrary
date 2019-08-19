<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "tw_componentlibrary"
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title'            => 'tollwerk TYPO3 Component Library',
    'description'      => 'Component library features for your TYPO3 project',
    'category'         => 'misc',
    'author'           => 'Joschi Kuphal',
    'author_email'     => 'joschi@tollwerk.de',
    'state'            => 'beta',
    'internal'         => '',
    'uploadfolder'     => '0',
    'createDirs'       => '',
    'clearCacheOnLoad' => 0,
    'version'          => '1.0.0',
    'constraints'      => array(
        'depends'   => array(
            'typo3' => '10.0.0-',
        ),
        'conflicts' => array(),
        'suggests'  => array(),
    ),
);
