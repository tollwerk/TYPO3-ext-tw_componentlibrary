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
use Alom\Graphviz\Node;
use Tollwerk\TwComponentlibrary\Component\ComponentInterface;

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
     * Graph component tree
     *
     * @var array
     */
    protected $graphComponentTree = [];
    /**
     * Component type colors
     *
     * @var array
     */
    protected static $colors = [
        ComponentInterface::TYPE_TYPOSCRIPT => 'khaki',
        ComponentInterface::TYPE_FLUID => 'lightblue',
        ComponentInterface::TYPE_EXTBASE => 'palegreen',
        ComponentInterface::TYPE_CONTENT => 'lightpink',
    ];

    /**
     * Constructor
     *
     * @param array $graphComponents Components
     */
    public function __construct(array $components)
    {
        $this->graphComponents = $this->buildGraphComponents($components);
        foreach ($this->graphComponents as $graphComponentId => $graphComponent) {
            if ($graphComponent['variant'] === null) {
                $this->addToComponentTree($graphComponentId, $graphComponent['path'], $this->graphComponentTree);
            }
        }
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
                'class' => null,
                'id' => null,
                'type' => null,
                'label' => null,
                'variant' => null,
                'master' => null,
                'variants' => [],
                'dependencies' => [],
                'path' => [],
            ]
        );

        // Run through all components
        /** @var array $component */
        foreach ($components as $component) {
            $graphComponents[$componentClass]['class'] = $componentClass = $component['class'];
            $graphComponents[$componentClass]['id'] = $component['name'];
            $graphComponents[$componentClass]['type'] = $component['type'];
            $graphComponents[$componentClass]['label'] = $component['label'] ?: $component['name'];
            $graphComponents[$componentClass]['variant'] = $component['variant'];
            $graphComponents[$componentClass]['path'] = $component['path'];

            // Link the component dependencies
            foreach ((new $componentClass)->getDependencies() as $componentDependency) {
                if (isset($graphComponents[$componentDependency])) {
                    $graphComponents[$componentClass]['dependencies'][$componentDependency] =& $graphComponents[$componentDependency];
                }
            }

            // If this is a variant: Register it with its master component
            if ($component['variant']) {
                list ($masterClass) = explode('_', $componentClass, 2);
                $masterClass .= 'Component';
                if (isset($graphComponents[$masterClass])) {
                    $graphComponents[$componentClass]['master'] =& $graphComponents[$masterClass];
                    $graphComponents[$masterClass]['variants'][$componentClass] =& $graphComponents[$componentClass];
                }
            }
        }

        return $graphComponents;
    }

    /**
     * Add a component to the component tree
     *
     * @param string $componentId Component ID
     * @param array $componentPath Component path
     * @param array $tree Component base tree
     */
    protected function addToComponentTree($componentId, array $componentPath, array &$tree)
    {
        // If the component has to be placed in a subpath
        if (count($componentPath)) {
            $subpath = array_shift($componentPath);
            if (empty($tree[$subpath])) {
                $tree[$subpath] = [];
            }
            $this->addToComponentTree($componentId, $componentPath, $tree[$subpath]);
            return;
        }

        $tree[] = $componentId;
    }

    /**
     * Create or update the dependency graph for a component
     *
     * @param null $rootComponent Optional: Root component
     * @return Digraph GraphViz digraph
     */
    public function __invoke($rootComponent = null)
    {
        $graph = new Digraph('G');
        $graph->attr('node', array('shape' => 'Mrecord', 'style' => 'radial', 'penwidth' => .5))
            ->set('rankdir', 'TB')
            ->set('ranksep', '0.5')
            ->set('nodesep', '.1')
            ->node(md5('/'), ['label' => '/']);

        return $this->addComponents($graph, $this->graphComponentTree, $graph->get(md5('/')))->render();
    }

    /**
     * Add a list of components to a graph
     *
     * @param BaseGraph $graph Graph
     * @param array $components Component list
     * @param Node $parentNode Parent node
     * @return BaseGraph Graph
     */
    protected function addComponents(BaseGraph $graph, array $components, Node $parentNode = null)
    {
        // Run through all components
        foreach ($components as $name => $component) {
            // If this is a subnode
            if (is_array($component)) {
                $this->addNode($graph, $name, $component, $parentNode);

                // Else: Regular component
            } else {
                $this->addComponent($graph, $component, $parentNode);
            }
        }

        return $graph;
    }

    /**
     * Add a node to a graph
     *
     * @param BaseGraph $graph Graph
     * @param string $nodeId Node ID
     * @param array $components Component list
     * @param Node $parentNode Parent node
     * @return BaseGraph Graph
     */
    protected function addNode(BaseGraph $graph, $nodeId, array $components, Node $parentNode = null)
    {
        $absNodeId = md5((($parentNode instanceof Node) ? $parentNode->getId() : md5('/')).$nodeId);
        $graph->node(
            $absNodeId,
            [
                'label' => $nodeId,
                'shape' => 'none',
                'style' => 'none',
            ]
        );

        if ($parentNode instanceof Node) {
            $graph->edge([$parentNode->getId(), $absNodeId], ['arrowhead' => 'none', 'color' => 'darkgrey', 'penwidth' => .5]);
        }

        return $this->addComponents($graph, $components, $graph->get($absNodeId));
    }

    /**
     * Add a component to a graph
     *
     * @param BaseGraph $graph Graph
     * @param string $componentId Component ID
     * @param Node $parentNode Parent node
     * @return BaseGraph Graph
     */
    protected function addComponent(BaseGraph $graph, $componentId, Node $parentNode = null)
    {
        $component =& $this->graphComponents[$componentId];
        $componentId = md5($componentId);
        $componentLabel = '<b>'.$component['label'].'</b><br align="left"/>';
        $escaped = false;
        $dependencyNodes = [];

        if (count($component['variants'])) {
            $componentLabel .= '<br align="left"/>';

            // Run through all variants
            foreach ($component['variants'] as $variant) {
                $componentLabel .= '&bull; '.$variant['label'].'<br align="left"/>';
                foreach ($variant['dependencies'] as $dependency) {
                    $dependencyNodes[md5($dependency['class'])] = true;
                }
            }
        }

        // Add the component node
        $graph->node(
            $componentId,
            [
                'label' => "<$componentLabel>",
                '_escaped' => $escaped,
                'margin' => '.15,.15',
                'fillcolor' => self::$colors[$component['type']],
            ]
        );

        // Add an edge from the parent node
        if ($parentNode instanceof Node) {
            $graph->edge([$parentNode->getId(), $componentId], ['arrowhead' => 'none', 'color' => 'darkgrey', 'penwidth' => .5]);
        }

        // Add dependency edges
        foreach ($component['dependencies'] as $dependency) {
            $dependencyNodes[md5($dependency['class'])] = true;
        }
        foreach (array_keys($dependencyNodes) as $dependencyNode) {
            $graph->edge([$componentId, $dependencyNode]);
        }

        return $graph;
    }
}
