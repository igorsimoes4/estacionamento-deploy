<?php

namespace App\Support;

class ItfBarcode
{
    private const DIGIT_PATTERNS = [
        '0' => 'nnwwn',
        '1' => 'wnnnw',
        '2' => 'nwnnw',
        '3' => 'wwnnn',
        '4' => 'nnwnw',
        '5' => 'wnwnn',
        '6' => 'nwwnn',
        '7' => 'nnnww',
        '8' => 'wnnwn',
        '9' => 'nwnwn',
    ];

    public static function renderSvg(string $value, int $height = 64, int $narrow = 2, int $wide = 5, int $margin = 8): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if ($digits === '') {
            return '';
        }

        // Interleaved 2 of 5 requires an even amount of digits.
        if ((strlen($digits) % 2) !== 0) {
            $digits = '0' . $digits;
        }

        $widths = [];

        // Start pattern: narrow bar, narrow space, narrow bar, narrow space.
        $widths[] = [$narrow, true];
        $widths[] = [$narrow, false];
        $widths[] = [$narrow, true];
        $widths[] = [$narrow, false];

        for ($i = 0; $i < strlen($digits); $i += 2) {
            $a = $digits[$i];
            $b = $digits[$i + 1];

            if (!isset(self::DIGIT_PATTERNS[$a], self::DIGIT_PATTERNS[$b])) {
                return '';
            }

            $patternA = self::DIGIT_PATTERNS[$a];
            $patternB = self::DIGIT_PATTERNS[$b];

            for ($j = 0; $j < 5; $j++) {
                $widths[] = [$patternA[$j] === 'w' ? $wide : $narrow, true];
                $widths[] = [$patternB[$j] === 'w' ? $wide : $narrow, false];
            }
        }

        // Stop pattern: wide bar, narrow space, narrow bar.
        $widths[] = [$wide, true];
        $widths[] = [$narrow, false];
        $widths[] = [$narrow, true];

        $totalBarsWidth = 0;
        foreach ($widths as [$w]) {
            $totalBarsWidth += $w;
        }

        $svgWidth = $totalBarsWidth + ($margin * 2);
        $svgHeight = max(24, $height);
        $x = $margin;

        $rects = '';
        foreach ($widths as [$w, $isBar]) {
            if ($isBar) {
                $rects .= '<rect x="' . $x . '" y="0" width="' . $w . '" height="' . $svgHeight . '" fill="#111" />';
            }
            $x += $w;
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $svgWidth . '" height="' . $svgHeight . '" viewBox="0 0 ' . $svgWidth . ' ' . $svgHeight . '" role="img" aria-label="Codigo de barras">' . $rects . '</svg>';
    }
}

