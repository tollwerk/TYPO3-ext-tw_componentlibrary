<?php

/**
 * Color System Utility
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

/**
 * Color Utility
 *
 * @package    Tollwerk\TwTollwerk
 * @subpackage Tollwerk\TwTollwerk\Utility
 */
class ColorUtility
{
    /**
     * Hex color grey tones
     *
     * @var string[]
     */
    const GREY_TONES_HEX = [
        0   => '#ffffff',
        10  => '#e3e3e3',
        20  => '#c7c7c7',
        30  => '#acacac',
        40  => '#909090',
        50  => '#757575',
        60  => '#5d5d5d',
        70  => '#464646',
        80  => '#2e2e2e',
        90  => '#171717',
        100 => '#000000'
    ];
    /**
     * RGB color grey tones
     *
     * @var array[]
     */
    const GREY_TONES_RGB = [
        0   => [255, 255, 255],
        10  => [227, 227, 227],
        20  => [199, 199, 199],
        30  => [172, 172, 172],
        40  => [144, 144, 144],
        50  => [117, 117, 117],
        60  => [93, 93, 93],
        70  => [70, 70, 70],
        80  => [46, 46, 46],
        90  => [23, 23, 23],
        100 => [0, 0, 0]
    ];
    /**
     * Grey tone luminances
     *
     * @var float[]
     */
    const GREY_TONES_LUMINANCE = [
        0   => 1.0,
        10  => 0.7681511472475069,
        20  => 0.5711248294648731,
        30  => 0.41254261348390375,
        40  => 0.27889426347681034,
        50  => 0.17788841598362912,
        60  => 0.10946171077829933,
        70  => 0.06124605423161761,
        80  => 0.027320891639074897,
        90  => 0.008568125618069307,
        100 => 0.0
    ];

    /**
     * Return the contrast between two colors
     *
     * @param array $color1 Color 1 (RGB channels)
     * @param array $color2 Color 2 (RGB channels)
     *
     * @return float Contrast
     */
    public static function contrast($color1, $color2): float
    {
        $color1   = array_pad(array_values($color1), 3, 0);
        $color2   = array_pad(array_values($color2), 3, 0);
        $contrast = (static::luminance($color1[0], $color1[1], $color1[2]) + 0.05)
                    / (static::luminance($color2[0], $color2[1], $color2[2]) + 0.05);

        return ($contrast >= 1) ? $contrast : (1 / $contrast);
    }

    /**
     * Calculate the luminance of a color
     *
     * @param int $red   Red value
     * @param int $green Green value
     * @param int $blue  Blue value
     *
     * @return float Luminance
     */
    public static function luminance($red, $green, $blue): float
    {
        list($red, $green, $blue) = array_map([static::class, 'channelLuminance'], func_get_args());
        $luminance = $red * 0.2126 + $green * 0.7152 + $blue * 0.0722;

        return $luminance;
    }

    /**
     * Return the channel luminance
     *
     * @param float $c Channel value
     *
     * @return float Channel luminance
     */
    public static function channelLuminance($c): float
    {
        $c /= 255;

        return ($c <= 0.03928) ? ($c / 12.92) : pow(($c + 0.055) / 1.055, 2.4);
    }

    /**
     * Create color system tones for a color
     *
     * @param string $color Hex color
     *
     * @return string[] Hex color tones
     */
    public static function tones(string $color)
    {
        list($colorHue, $colorSaturation) = array_values(static::hex2hsl($color));
        $tonesLuminance    = self::GREY_TONES_LUMINANCE;
        $colorTones        = [];
        $lightness         = 100;
        $lastToneLuminance = null;
        $lastToneRgb       = null;

        // Process all tone luminances
        while (count($tonesLuminance)) {
            $luminance = array_shift($tonesLuminance);
            while ($lightness >= 0) {
                $toneRGB       = static::hsl2Rgb($colorHue, $colorSaturation, $lightness / 100);
                $toneLuminance = static::luminance(...$toneRGB);

                // If the lightness matches exactly
                if ($toneLuminance == $luminance) {
                    $colorTones[]      = static::rgb2hex(...$toneRGB);
                    $lastToneRgb       = $toneRGB;
                    $lastToneLuminance = $toneLuminance;
                    --$lightness;
                    continue 2;
                }

                // If the current tone luminance passed the luminance threshold
                if ($toneLuminance < $luminance) {
                    $luminanceDistance     = abs($toneLuminance - $luminance);
                    $lastLuminanceDistance = abs($lastToneLuminance - $luminance);
                    if ($luminanceDistance > $lastLuminanceDistance) {
//                echo $lastToneLuminance.' << '.$luminance.' || '.$toneLuminance.PHP_EOL;
                        $colorTones[] = static::rgb2hex(...$lastToneRgb);
                    } else {
//                echo $lastToneLuminance.' || '.$luminance.' >> '.$toneLuminance.PHP_EOL;
                        $colorTones[] = static::rgb2hex(...$toneRGB);
                        --$lightness;
                    }
                    $lastToneRgb       = $toneRGB;
                    $lastToneLuminance = $toneLuminance;
                    continue 2;
                }

                $lastToneRgb       = $toneRGB;
                $lastToneLuminance = $toneLuminance;
                --$lightness;
            }
        }

        return array_combine(range(0, count($colorTones) * 10 - 10, 10), $colorTones);
    }

    /**
     * Convert a hex color to HSL
     *
     * @param $hex Hex color
     *
     * @return float[] HSL color
     */
    public static function hex2hsl($hex): array
    {
        return self::rgb2hsl(...self::hex2rgb($hex));
    }

    /**
     * Convert an RGB color to HSL
     *
     * @param int $r Red value
     * @param int $g Green value
     * @param int $b Blue value
     *
     * @return float[]
     */
    public static function rgb2hsl($r, $g, $b): array
    {
        $r   /= 255;
        $g   /= 255;
        $b   /= 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l   = ($max + $min) / 2;
        $d   = $max - $min;

        if ($d == 0) {
            $h = $s = 0;

        } else {
            $s = $d / (1 - abs(2 * $l - 1));
            switch ($max) {
                case $r:
                    $h = 60 * fmod((($g - $b) / $d), 6);
                    if ($b > $g) {
                        $h += 360;
                    }
                    break;

                case $g:
                    $h = 60 * (($b - $r) / $d + 2);
                    break;

                case $b:
                    $h = 60 * (($r - $g) / $d + 4);
                    break;
            }
        }

        return ['h' => round($h, 2), 's' => round($s, 2), 'l' => round($l, 2)];
    }

    /**
     * Convert a hex color to RGB
     *
     * @param $hex Hex color
     *
     * @return int[] RGB color
     */
    public static function hex2rgb($hex): array
    {
        return array_map('intval', sscanf($hex, "#%02x%02x%02x"));
    }

    /**
     * Convert a HSL color to RGB
     *
     * @param float $h Hue
     * @param float $s Saturation
     * @param float $l Lightness
     *
     * @return int[] RGB
     */
    public static function hsl2Rgb($h, $s, $l): array
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod(($h / 60), 2) - 1));
        $m = $l - ($c / 2);
        if ($h < 60) {
            $r = $c;
            $g = $x;
            $b = 0;
        } else {
            if ($h < 120) {
                $r = $x;
                $g = $c;
                $b = 0;
            } else {
                if ($h < 180) {
                    $r = 0;
                    $g = $c;
                    $b = $x;
                } else {
                    if ($h < 240) {
                        $r = 0;
                        $g = $x;
                        $b = $c;
                    } else {
                        if ($h < 300) {
                            $r = $x;
                            $g = 0;
                            $b = $c;
                        } else {
                            $r = $c;
                            $g = 0;
                            $b = $x;
                        }
                    }
                }
            }
        }
        $r = min(255, max(0, ($r + $m) * 255));
        $g = min(255, max(0, ($g + $m) * 255));
        $b = min(255, max(0, ($b + $m) * 255));

        return [floor($r), floor($g), floor($b)];
    }

    /**
     * Convert an RGB color to hex
     *
     * @param int $r Red
     * @param int $g Green
     * @param int $b $blue
     *
     * @return string Hex color
     */
    public static function rgb2hex($r, $g, $b): string
    {
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
}



