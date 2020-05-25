<?php

/**
 * Component dependency graph utility
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Utility
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

namespace Tollwerk\TwComponentlibrary\Utility;

use Alom\Graphviz\Digraph;
use Alom\Graphviz\Graph as BaseGraph;
use Alom\Graphviz\Node;
use Tollwerk\TwComponentlibrary\Component\ComponentInterface;

/**
 * Component dependency graph utility
 *
 * `vendor/bin/typo3 component:graph
 *
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Utility
 */
class Graph
{
    /**
     * Component type colors
     *
     * @var array
     */
    protected static $colors = [
        ComponentInterface::TYPE_TYPOSCRIPT => 'khaki',
        ComponentInterface::TYPE_FLUID      => 'lightblue',
        ComponentInterface::TYPE_EXTBASE    => 'palegreen',
        ComponentInterface::TYPE_CONTENT    => 'lightpink',
    ];
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
     * Root component
     *
     * @var null|string
     */
    protected $rootComponent = null;

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
     *
     * @return array Graph components
     */
    protected function buildGraphComponents(array $components)
    {
        $graphComponents = array_fill_keys(
            array_column($components, 'class'),
            [
                'class'        => null,
                'id'           => null,
                'type'         => null,
                'label'        => null,
                'variant'      => null,
                'master'       => null,
                'variants'     => [],
                'dependencies' => [],
                'path'         => [],
            ]
        );

        // Run through all components
        /** @var array $component */
        foreach ($components as $component) {
            $graphComponents[$componentClass]['class']   = $componentClass = $component['class'];
            $graphComponents[$componentClass]['id']      = $component['name'];
            $graphComponents[$componentClass]['type']    = $component['type'];
            $graphComponents[$componentClass]['label']   = $component['label'] ?: $component['name'];
            $graphComponents[$componentClass]['variant'] = $component['variant'];
            $graphComponents[$componentClass]['path']    = $component['path'];

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
                    $graphComponents[$componentClass]['master']                 =& $graphComponents[$masterClass];
                    $graphComponents[$masterClass]['variants'][$componentClass] =& $graphComponents[$componentClass];
                }
            }
        }

        return $graphComponents;
    }

    /**
     * Add a component to the component tree
     *
     * @param string $componentId  Component ID
     * @param array $componentPath Component path
     * @param array $tree          Component base tree
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
     *
     * @return Digraph GraphViz digraph
     */
    public function __invoke($rootComponent = null)
    {
        $graph = new Digraph('Components');
        $graph->attr('node', array('shape' => 'Mrecord', 'style' => 'radial', 'penwidth' => .5))
              ->set('rankdir', 'TB')
              ->set('bgcolor', 'transparent')
//            ->set('ranksep', '0.5')
//            ->set('nodesep', '.1')
              ->node('/');

        // If the complete component graph should be rendered
        $this->rootComponent = trim($rootComponent) ?: null;
        if ($this->rootComponent === null) {
            $graphComponentTree = $this->graphComponentTree;

            // Else: Build a component tree subset
        } else {
            $componentIds           = [$this->rootComponent];
            $registeredComponentIds = [];
            $graphComponentTree     = [];
            do {
                $graphComponentTreePointer =& $graphComponentTree;
                $componentId               = $registeredComponentIds[] = array_shift($componentIds);
                $componentIds              = array_diff(
                    array_unique(
                        array_merge(
                            $componentIds,
                            $this->addToComponentTreeSubset($graphComponentTreePointer, $componentId)
                        )
                    ),
                    $registeredComponentIds
                );

            } while (count($componentIds));
        }

        return $this->addComponents($graph, $graphComponentTree, $graph->get('/'))->render();
    }

    /**
     * Recursively add a single component to a component tree subset
     *
     * @param array $pointer      Component tree subset
     * @param string $componentId Component class
     *
     * @return array Component dependencies
     */
    protected function addToComponentTreeSubset(array &$pointer, $componentId)
    {
        foreach ($this->graphComponents[$componentId]['path'] as $node) {
            if (empty($pointer[$node])) {
                $pointer[$node] = [];
            }
            $pointer =& $pointer[$node];
        }
        $pointer[] = $componentId;

        if ($this->graphComponents[$componentId]['master']) {
            $dependencies = array_column($this->graphComponents[$componentId]['master']['dependencies'], 'class');
            foreach ($this->graphComponents[$componentId]['master']['variants'] as $variant) {
                $dependencies = array_column($variant['dependencies'], 'class');
            }
        } else {
            $dependencies = array_column($this->graphComponents[$componentId]['dependencies'], 'class');
        }

        return array_unique($dependencies);
    }

    /**
     * Add a list of components to a graph
     *
     * @param BaseGraph $graph  Graph
     * @param array $components Component list
     * @param Node $parentNode  Parent node
     *
     * @return BaseGraph Graph
     */
    protected function addComponents(BaseGraph $graph, array $components, Node $parentNode = null)
    {
        // Sort the components
//        uasort($components, function($component1, $component2) {
//            $isNode1 = is_array($component1);
//            $isNode2 = is_array($component2);
//
//            // If both are of the same type
//            if ($isNode1 == $isNode2) {
//                return 0;
//
//                // Else
//            } else {
//                return $isNode1 ? 1 : -1;
//            }
//        });

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
     * @param BaseGraph $graph  Graph
     * @param string $nodeId    Node ID
     * @param array $components Component list
     * @param Node $parentNode  Parent node
     *
     * @return BaseGraph Graph
     */
    protected function addNode(BaseGraph $graph, $nodeId, array $components, Node $parentNode = null)
    {
        $absNodeId = (($parentNode instanceof Node) ? $parentNode->getId() : '/').$nodeId;
        $graph->node(
            $absNodeId,
            [
                'label' => $nodeId,
                'shape' => 'none',
                'style' => 'none',
            ]
        );

        if ($parentNode instanceof Node) {
            $graph->edge(
                [$parentNode->getId(), $absNodeId], ['arrowhead' => 'none', 'color' => 'darkgrey', 'penwidth' => .5]
            );
        }

        return $this->addComponents($graph, $components, $graph->get($absNodeId));
    }

    /**
     * Add a component to a graph
     *
     * @param BaseGraph $graph    Graph
     * @param string $componentId Component ID
     * @param Node $parentNode    Parent node
     *
     * @return BaseGraph Graph
     */
    protected function addComponent(BaseGraph $graph, $componentId, Node $parentNode = null)
    {
        $component          = $this->graphComponents[$componentId];
        $origComponentClass = $component['class'];

        // If this is a variant: Redirect to the master component
        if ($component['master']) {
            $component      = $component['master'];
            $componentId    = $component['class'];
            $componentLabel = htmlspecialchars($component['label']);

            // Else: This is a master component
        } else {
            $componentLabel = '<b>'.htmlspecialchars($component['label']).'</b>';
        }

        $componentLabel  .= '<br align="left"/>';
        $escaped         = false;
        $dependencyNodes = [];

        if (count($component['variants'])) {
            uasort(
                $component['variants'],
                function(array $variant1, array $variant2) {
                    return strnatcasecmp($variant1['label'], $variant2['label']);
                }
            );

            // Run through all variants
            $variantCount = count($component['variants']);
            foreach (array_values($component['variants']) as $index => $variant) {
                $variantLabel = '• '.htmlspecialchars($variant['label']);
                if ($variant['class'] === $origComponentClass) {
                    $variantLabel = "<b>$variantLabel</b>";
                }
                $componentLabel .= $variantLabel.'<br align="left"/>';
                foreach ($variant['dependencies'] as $dependency) {
                    $dependencyNodes[$dependency['class']] = true;
                }
            }
        }

        $isCurrent = $this->rootComponent === $origComponentClass;

        // Add the component node
        $graph->node(
            $this->getComponentTitle($componentId),
            [
                'label'     => "<$componentLabel>",
                '_escaped'  => $escaped,
                'margin'    => '.15,.15',
                'fillcolor' => self::$colors[$component['type']],
                'penwidth'  => $isCurrent ? 1.5 : .5,
            ]
        );

        // Add an edge from the parent node
        if ($parentNode instanceof Node) {
            $graph->edge(
                [$parentNode->getId(), $this->getComponentTitle($componentId)],
                ['arrowhead' => 'none', 'color' => 'darkgrey', 'penwidth' => .5]
            );
        }

        // Add dependency edges
        foreach ($component['dependencies'] as $dependency) {
            $dependencyNodes[$dependency['master'] ? $dependency['master']['class'] : $dependency['class']] = true;
        }
        foreach (array_keys($dependencyNodes) as $dependencyNode) {
            $graph->edge([$this->getComponentTitle($componentId), $this->getComponentTitle($dependencyNode)]);
        }

        return $graph;
    }

    /**
     * Create and return an component title
     *
     * @param string $componentId Component ID
     *
     * @return string Component title
     */
    protected function getComponentTitle($componentId)
    {
        $component      =& $this->graphComponents[$componentId];
        $componentTitle = implode('/', array_filter(array_merge($component['path'], [$component['id']])));
        $componentTitle .= ' ('.$component['class'].')';

        return $componentTitle;
    }
}
