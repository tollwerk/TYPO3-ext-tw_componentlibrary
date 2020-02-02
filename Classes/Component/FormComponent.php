<?php

/**
 * FLUIDTEMPLATE component
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
use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Form\Domain\Configuration\ConfigurationService;
use TYPO3\CMS\Form\Domain\Configuration\Exception\PrototypeNotFoundException;
use TYPO3\CMS\Form\Domain\Exception\RenderingException;
use TYPO3\CMS\Form\Domain\Exception\TypeDefinitionNotFoundException;
use TYPO3\CMS\Form\Domain\Exception\TypeDefinitionNotValidException;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Model\Renderable\AbstractRenderable;
use TYPO3\CMS\Form\Domain\Renderer\FluidFormRenderer;
use TYPO3\CMS\Form\Domain\Renderer\RendererInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Abstract FLUIDTEMPLATE component
 *
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component
 */
abstract class FormComponent extends AbstractComponent
{
    /**
     * Component type
     *
     * @var string
     */
    protected $type = self::TYPE_FORM;
    /**
     * Form definition
     *
     * @var FormDefinition
     */
    protected $form;
    /**
     * Form page
     *
     * @var Page
     */
    protected $page;
    /**
     * Form element
     *
     * @var AbstractRenderable
     */
    protected $element = null;
    /**
     * Translation file for form elements
     *
     * @var string
     */
    protected $translationFile = null;

    /**
     * Render this component
     *
     * @return string Rendered component (HTML)
     * @throws InvalidArgumentNameException
     * @throws InvalidExtensionNameException
     */
    public function render(): string
    {
        // Set the request arguments as GET parameters
        $_GET = $this->getRequestArguments();

        try {
            $rendererClassName = $this->element->getRendererClassName();

            /** @var FluidFormRenderer $renderer */
            $renderer = $this->objectManager->get($rendererClassName);
            if (!($renderer instanceof RendererInterface)) {
                throw new RenderingException(
                    sprintf('The renderer "%s" des not implement RendererInterface', $rendererClassName),
                    1326096024
                );
            }
            $renderer->setControllerContext($this->controllerContext);

            $this->controllerContext->getRequest()->setOriginalRequestMappingResults($this->validationErrors);
            $response = $this->objectManager->get(Response::class, $this->controllerContext->getResponse());
            /** @var FormRuntime $form */
            $form = $this->form->bind($this->controllerContext->getRequest(), $response);
            $renderer->setFormRuntime($form);

            $result = $this->beautify($renderer->render(), true);

            // In case of an error
        } catch (Exception $e) {
            $result = '<pre class="error"><strong>'.$e->getMessage().'</strong>'.PHP_EOL
                      .$e->getTraceAsString().'</pre>';
        }

        return $result;
    }

    /**
     * Initialize the component
     *
     * Gets called immediately after construction. Override this method in components to initialize the component.
     *
     * @return void
     * @throws PrototypeNotFoundException
     * @throws TypeDefinitionNotFoundException
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    protected function initialize()
    {
        parent::initialize();

        // Use the standard language file as fallback
        if ($this->translationFile === null) {
            $this->translationFile = 'EXT:'.$this->extensionKey.'/Resources/Private/Language/locallang.xlf';
        }

        $configurationService   = $this->objectManager->get(ConfigurationService::class);
        $prototypeConfiguration = $configurationService->getPrototypeConfiguration('standard');
        $this->form             = $this->objectManager->get(
            FormDefinition::class,
            'form',
            $prototypeConfiguration
        );
        $this->form->setRenderingOption('translation', ['translationFile' => $this->translationFile]);
        $this->form->setRenderingOption('honeypot', ['enable' => false]);
        $this->page = $this->form->createPage('page');

        $this->preview->setTemplateName('Form');
    }

    /**
     * Create a form element
     *
     * @param string $typeName        Type of the new form element
     * @param string $identifier      Form element identifier
     * @param array $properties       Element properties
     * @param array $renderingOptions Rendering options
     *
     * @return AbstractRenderable Renderable form element
     * @throws TypeDefinitionNotFoundException
     * @throws TypeDefinitionNotValidException
     */
    protected function createElement($typeName, $identifier = null, $properties = [], $renderingOptions = [])
    {
        $identifier    = trim($identifier) ?:
            strtr(GeneralUtility::camelCaseToLowerCaseUnderscored($typeName), '_', '-').'-1';
        $this->element = $this->page->createElement($identifier, $typeName);
        $this->element->setProperty('fluidAdditionalAttributes', []);

        foreach ($properties as $key => $value) {
            $this->element->setProperty($key, $value);
        }

        foreach ($renderingOptions as $key => $value) {
            $this->element->setRenderingOption($key, $value);
        }

        return $this->element;
    }

    /**
     * Register a validation error for the form element
     *
     * @param string $message Validation error message
     *
     * @throws RuntimeException If no element has been created prior to adding an error message
     */
    protected function addElementError($message)
    {
        if ($this->element === null) {
            throw new RuntimeException('Create a form element prior to adding a validation error', 1519731421);
        }
        $this->addError($this->element->getIdentifier(), $message);
    }

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
     * Return component specific properties
     *
     * @return array Component specific properties
     */
    protected function exportInternal()
    {
        // Read the linked TypoScript
        if ($this->config !== null) {
            $templateFile = GeneralUtility::getFileAbsFileName($this->config);
            if (!strlen($templateFile) || !is_file($templateFile)) {
                throw new RuntimeException(sprintf('Invalid template file "%s"', $templateFile), 1481552328);
            }
            $this->template = file_get_contents($templateFile);
            $this->config   = [
                'type'             => $this->element->getType(),
                'properties'       => $this->element->getProperties(),
                'renderingOptions' => $this->element->getRenderingOptions(),
                'template'         => $templateFile,
            ];
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
        $resources = parent::getResources();

        if (strlen($this->config)) {
            $resources[] = $this->config;
        }

        return $resources;
    }
}
