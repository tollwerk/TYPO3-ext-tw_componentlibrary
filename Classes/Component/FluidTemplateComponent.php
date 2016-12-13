<?php

/**
 * FLUIDTEMPLATE component
 *
 * @category Tollwerk
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 * @author Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2016 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  Copyright © 2016 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FLUIDTEMPLATE component
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 */
class FluidTemplateComponent extends AbstractComponent
{
    /**
     * Component type
     *
     * @var string
     */
    protected $type = 'fluid';
    /**
     * Parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Set the fluid template
     *
     * @param $template
     */
    protected function setTemplate($template)
    {
        $this->config = trim($template) ?: null;
    }

    /**
     * Set a rendering parameter
     *
     * @param string $param Parameter name
     * @param mixed $value Parameter value
     * @throws \RuntimeException If the parameter name is invalid
     */
    protected function setParameter($param, $value)
    {
        $param = trim($param);
        if (!strlen($param)) {
            throw new \RuntimeException(sprintf('Invalid fluid template parameter "%s"', $param), 1481551574);
        }

        $this->parameters[$param] = $value;
    }

    /**
     * Return component specific properties
     *
     * Override this method in sub classes to export specific properties.
     *
     * @return array Component specific properties
     */
    protected function exportInternal()
    {
        // Read the linked TypoScript
        if ($this->config !== null) {
            $templateFile = GeneralUtility::getFileAbsFileName($this->config);
            if (!strlen($templateFile) || !is_file($templateFile)) {
                throw new \RuntimeException(sprintf('Invalid template file "%s"', $templateFile), 1481552328);
            }
            $this->template = file_get_contents($templateFile);
        }

        return array_merge(
            ['parameters' => $this->parameters],
            parent::exportInternal()
        );
    }

    /**
     * Render this component
     *
     * @return string Rendered component (HTML)
     */
    public function render()
    {
        // Simulate Frontend mode
        $this->environmentService->simulateFrontendMode(true);

        // Set the request arguments as GET parameters
        $_GET = $this->getRequestArguments();

        // Instantiate a frontend controller
        $TSFE = TypoScriptUtility::getTSFE($this->page, $this->typeNum);

        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->config));
        $view->assignMultiple($this->parameters);
        $result = $view->render();

        // Stop simulating Frontend mode
        $this->environmentService->simulateFrontendMode(false);

        return $result;
    }
}
