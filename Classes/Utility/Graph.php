<?php

/**
 * Component dependency graph utility
 *
 * @category Tollwerk
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Utility
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

namespace Tollwerk\TwComponentlibrary\Utility;

use Alom\Graphviz\Digraph;
use Alom\Graphviz\Graph as BaseGraph;

/**
 * Component dependency graph utility
 *
 * `typo3/cli_dispatch.phpsh extbase component:graph
 *
 * @package Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Utility
 */
class Graph
{
    /**
     * Graph components
     *
     * @var array
     */
    protected $graphComponents = [];

    /**
     * Constructor
     *
     * @param array $graphComponents Components
     */
    public function __construct(array $components)
    {
        $this->graphComponents = $this->buildGraphComponents($components);
    }

    /**
     * Create or update the dependency graph for a component
     *
     * @param null $rootComponent Optional: Root component
     * @return Digraph GraphViz digraph
     */
    public function __invoke($rootComponent = null)
    {
        $graph = new Digraph();
        $rootComponents = ($rootComponent === null) ? array_keys($this->graphComponents) : [$rootComponent];
        $rootComponents = array_combine($rootComponents, $rootComponents);

        // While there are root components
        while (count($rootComponents)) {
            $rootComponent = array_shift($rootComponents);
            $this->addComponentGraph($graph, $this->graphComponents[$rootComponent], $rootComponents);
        }

        return $graph->render();
    }

    /**
     * Add a single component to the graph
     *
     * @param BaseGraph $graph GraphViz BaseGraph
     * @param array $component Component properties
     * @param array $rootComponents Remaining root components
     * @return BaseGraph GraphViz BaseGraph
     */
    protected function addComponentGraph(BaseGraph $graph, array $component, array &$rootComponents)
    {
        // If this is a master component with variants
        if (count($component['variants'])) {
            return $this->addMasterComponentGraph($graph, $component, $rootComponents);
        }

        // Add if it's not a variant
        if ($component['master'] === null) {
            return $this->addVariantComponentGraph($graph, $component, $rootComponents);
        }

        return $graph;
    }

    /**
     * Add a master component with variants to the graph
     *
     * @param BaseGraph $graph GraphViz BaseGraph
     * @param array $component Master component properties
     * @param array $rootComponents Remaining root components
     * @return BaseGraph GraphViz BaseGraph
     */
    protected function addMasterComponentGraph(BaseGraph $graph, array $component, array &$rootComponents)
    {
        $graph = $graph->subgraph(md5($component['id']))
            ->node($component['id']);

        // Run through all variants
        foreach ($component['variants'] as $variantClass => $variantProperties) {
            $this->addVariantComponentGraph($graph, $variantProperties, $rootComponents);
            unset($rootComponents[$variantClass]);
        }

        return $this->addDependenciesComponentGraph($graph, $component, $rootComponents)->end();
    }

    /**
     * Add a variant component to the graph
     *
     * @param BaseGraph $graph GraphViz BaseGraph
     * @param array $component Variant component properties
     * @param array $rootComponents Remaining root components
     * @return BaseGraph GraphViz BaseGraph
     */
    protected function addVariantComponentGraph(BaseGraph $graph, array $component, array &$rootComponents)
    {
        $graph->node($component['id']);
        return $this->addDependenciesComponentGraph($graph, $component, $rootComponents);
    }

    /**
     * Add a component dependencies to the graph
     *
     * @param BaseGraph $graph GraphViz BaseGraph
     * @param array $component Variant component properties
     * @param array $rootComponents Remaining root components
     * @return BaseGraph GraphViz BaseGraph
     */
    protected function addDependenciesComponentGraph(BaseGraph $graph, array $component, array &$rootComponents)
    {
        // Draw the edges to all dependencies
        foreach ($component['dependencies'] as $dependency) {
            $graph->edge(array($component['id'], $dependency['id']));
        }

        return $graph;
    }

    /**
     * Prepare the list of graph components
     *
     * @param array $components Components
     * @return array Graph components
     */
    protected function buildGraphComponents(array $components)
    {
        $components = Scanner::discoverAll();
        $graphComponents = array_fill_keys(
            array_column($components, 'class'),
            [
                'id' => null,
                'name' => null,
                'label' => null,
                'variant' => null,
                'master' => null,
                'variants' => [],
                'dependencies' => [],
                'dependents' => 0,
                'path' => [],
            ]
        );

        // Run through all components
        /** @var array $component */
        foreach ($components as $component) {
            $componentClass = $component['class'];
            $graphComponents[$componentClass]['id'] = implode('/',
                array_filter(array_merge($component['path'], [$component['name']])));
            $graphComponents[$componentClass]['name'] = $component['name'];
            $graphComponents[$componentClass]['label'] = $component['label'] ?: $component['name'];
            $graphComponents[$componentClass]['variant'] = $component['variant'];
            $graphComponents[$componentClass]['path'] = $component['path'];

            // Link the component dependencies
            foreach ((new $componentClass)->getDependencies() as $componentDependency) {
                if (isset($graphComponents[$componentDependency])) {
                    $graphComponents[$componentClass]['dependencies'][$componentDependency] =& $graphComponents[$componentDependency];
                    ++$graphComponents[$componentDependency]['dependents'];
                }
            }

            // If this is a variant: Register it with its master component
            if ($component['variant']) {
                $graphComponents[$componentClass]['id'] .= '-'.$component['variant'];
                list ($masterClass) = explode('_', $componentClass, 2);
                $masterClass .= 'Component';
                if (isset($graphComponents[$masterClass])) {
                    $graphComponents[$componentClass]['master'] =& $graphComponents[$masterClass];
                    $graphComponents[$masterClass]['variants'][$componentClass] =& $graphComponents[$componentClass];
                }
            }
        }

        // Sort the components
        uasort($graphComponents, [$this, 'sortComponents']);

        return $graphComponents;
    }

    /**
     * Component comparison
     *
     * @param array $component1 Component 1
     * @param array $component2 Component 1
     * @return int Sorting
     */
    protected function sortComponents(array $component1, array $component2)
    {
        // If both components serve as dependency for the same number of components
        if ($component1['dependents'] == $component2['dependents']) {
            return strnatcmp($component1['label'], $component2['label']);
        } else {
            return ($component1['dependents'] > $component2['dependents']) ? 1 : -1;
        }
    }
}
