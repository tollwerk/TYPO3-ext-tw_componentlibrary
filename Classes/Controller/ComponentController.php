<?php

/**
 * Component controller
 *
 * @category Tollwerk
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Controller
 * @author Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

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

namespace Tollwerk\TwComponentlibrary\Controller;

use Tollwerk\TwComponentlibrary\Component\ComponentInterface;
use Tollwerk\TwComponentlibrary\Component\Preview\BasicTemplate;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Component controller
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Controller
 */
class ComponentController extends ActionController
{
    /**
     * Render a component
     *
     * @param string $component Component class
     */
    public function renderAction($component)
    {
        // Register common stylesheets & scripts
        BasicTemplate::addCommonStylesheets($this->settings['stylesheets']);
        BasicTemplate::addCommonHeaderScripts($this->settings['headerScripts']);
        BasicTemplate::addCommonFooterScripts($this->settings['footerScripts']);

        $componentInstance = $this->objectManager->get($component);
        if ($componentInstance instanceof ComponentInterface) {
            return trim($componentInstance->render());
        }

        return $this->view->render();
    }
}
