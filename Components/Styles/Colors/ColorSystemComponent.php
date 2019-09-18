<?php

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

namespace Tollwerk\TwComponentlibrary\Component\Styles\Colors;

use Tollwerk\TwComponentlibrary\Component\FluidTemplateComponent;
use Tollwerk\TwComponentlibrary\Utility\ColorUtility;

/**
 * Color System Component
 *
 * @package    Tollwerk\TwComponentlibrary
 * @subpackage Tollwerk\TwComponentlibrary\Component\Styles\Colors
 */
abstract class ColorSystemComponent extends FluidTemplateComponent
{
    /**
     * Label
     *
     * @var string
     */
    protected $label = 'Color System';
    /**
     * Colors
     *
     * @var array[]
     */
    protected $hues = [];
    /**
     * Primary colors
     *
     * @var string[]
     */
    protected $primaryColors = ['grey' => '#757575'];

    /**
     * Configure the component
     *
     * Gets called immediately after construction. Override this method in components to set their properties.
     *
     * @return void
     */
    protected function configure()
    {
        $this->addToneScales($this->primaryColors);
        $this->setTemplate('EXT:tw_componentlibrary/Resources/Private/Partials/Styles/Colors/System.html');
        $this->setParameter('hues', $this->hues);
        $this->preview->addStylesheet('EXT:tw_componentlibrary/Resources/Public/Styles/_Colors.min.css');
    }

    /**
     * Add tone scales
     *
     * @var string[] $colors Original colors
     */
    protected function addToneScales(array $colors): void
    {
        foreach ($colors as $name => $color) {
            $this->hues[$name] = $this->expandTones($color, ColorUtility::tones($color));
        }
    }

    /**
     * Expand a set of hex colors with the RGB and HSL values
     *
     * @param string $original Original color
     * @param string[] $tones  Hex colors
     *
     * @return array[] Expanded colors
     */
    protected function expandTones(string $original, array $tones): array
    {
        $expanded  = ['original' => ['background' => $original], 'tones' => []];
        $hexColors = array_values($tones);
        foreach ($tones as $name => $hex) {
            $count                    = count($expanded['tones']);
            $rgb                      = ColorUtility::hex2rgb($hex);
            $foreground               = $hexColors[($count > 4) ? ($count - 5) : ($count + 5)];
            $expanded['tones'][$name] = array_merge(
                [
                    'hex'        => strtoupper($hex),
                    'foreground' => $foreground,
                    'luminance'  => round(ColorUtility::luminance(...$rgb), 12),
                    'white'      => number_format(ColorUtility::contrast(array_values($rgb), [255, 255, 255]), 2),
                    'black'      => number_format(ColorUtility::contrast(array_values($rgb), [0, 0, 0]), 2),
                ], $rgb, ColorUtility::rgb2hsl(...$rgb)
            );
        }

        $originalRgb                        = ColorUtility::hex2rgb($original);
        $originalLuminance                  = ColorUtility::luminance(...$originalRgb);
        $expanded['original']['foreground'] = ($originalLuminance > $expanded['tones'][50]['luminance']) ?
            '#000' : '#fff';
        $expanded['original']['white']      = number_format(
            ColorUtility::contrast(array_values($originalRgb), [255, 255, 255]),
            2
        );
        $expanded['original']['black']      = number_format(
            ColorUtility::contrast(array_values($originalRgb), [0, 0, 0]),
            2
        );
        $expanded['original']['luminance']  = round($originalLuminance, 12);

        return $expanded;
    }
}
