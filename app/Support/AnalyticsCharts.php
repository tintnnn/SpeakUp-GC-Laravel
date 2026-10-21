<?php

namespace App\Support;

/**
 * Server-side stand-in for the Recharts line and column charts in
 * frontend/src/views/admin/Analytics.tsx. Colors match utils/chartColors.ts.
 */
class AnalyticsCharts
{
    private const CATEGORY = ['#0F6E56', '#1D9E75', '#4DB894', '#7DCEB3', '#9FE1CB', '#7A8F82'];

    public static function line(iterable $rows, string $valueKey, string $color, string $series): string
    {
        $points = self::points($rows, 'date', $valueKey);
        if ($points === []) {
            return '';
        }

        [$width, $height, $left, $right, $top, $bottom] = [640, 280, 40, 12, 16, 32];
        $plotW = $width - $left - $right;
        $plotH = $height - $top - $bottom;
        $yMax = self::niceMax(max(array_column($points, 'value')));
        $ticks = self::ticks($yMax);
        $count = count($points);
        $coords = [];

        foreach ($points as $index => $point) {
            $x = $left + ($count === 1 ? $plotW / 2 : ($index / ($count - 1)) * $plotW);
            $y = $top + $plotH - ($point['value'] / $yMax) * $plotH;
            $coords[] = [$x, $y, $point];
        }

        $svg = self::open($width, $height, $series.' over time');
        $svg .= self::grid($ticks, $yMax, $left, $top, $plotW, $plotH, $width - $right);
        $svg .= '<path d="'.self::smooth($coords).'" fill="none" stroke="'.$color.'" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />';

        foreach ($coords as [$x, $y, $point]) {
            $tip = e($point['label'].': '.$point['value'].' '.$series);
            $svg .= '<circle cx="'.self::n($x).'" cy="'.self::n($y).'" r="3.5" fill="'.$color.'"><title>'.$tip.'</title></circle>';
            $svg .= self::label($point['label'], $x, $height - 10, 'middle');
        }

        return $svg.'</svg>';
    }

    public static function columns(iterable $rows, string $labelKey, string $valueKey, string $colorMode, string $series): string
    {
        $points = self::points($rows, $labelKey, $valueKey);
        if ($points === []) {
            return '';
        }

        [$width, $height, $left, $right, $top, $bottom] = [640, 300, 40, 16, 16, 78];
        $plotW = $width - $left - $right;
        $plotH = $height - $top - $bottom;
        $yMax = self::niceMax(max(array_column($points, 'value')));
        $ticks = self::ticks($yMax);
        $count = count($points);
        $slot = $plotW / $count;
        $barW = min(56, $slot * 0.55);

        $svg = self::open($width, $height, $series.' by group');
        $svg .= self::grid($ticks, $yMax, $left, $top, $plotW, $plotH, $width - $right);

        foreach ($points as $index => $point) {
            $color = self::color($colorMode, $point['label'], $index);
            $cx = $left + $slot * $index + $slot / 2;
            $barH = max($point['value'] > 0 ? 4 : 0, ($point['value'] / $yMax) * $plotH);
            $x = $cx - $barW / 2;
            $y = $top + $plotH - $barH;
            $tip = e($point['label'].': '.$point['value'].' '.$series);
            $svg .= self::roundedBar($x, $y, $barW, $barH, 6, $color, $tip);
            if ($point['value'] > 0) {
                $svg .= self::label((string) $point['value'], $cx, $y - 6, 'middle', '#374151');
            }
            $svg .= '<text transform="rotate(-28 '.self::n($cx).' '.self::n($top + $plotH + 16).')" x="'.self::n($cx).'" y="'.self::n($top + $plotH + 16).'" text-anchor="end" fill="#6b7280" font-size="11" font-family="Sora, ui-sans-serif, sans-serif">'.e(self::short($point['label'])).'<title>'.e($point['label']).'</title></text>';
        }

        return $svg.'</svg>';
    }

    private static function points(iterable $rows, string $labelKey, string $valueKey): array
    {
        $points = [];
        foreach ($rows as $row) {
            $points[] = [
                'label' => (string) data_get($row, $labelKey, ''),
                'value' => (int) data_get($row, $valueKey, 0),
            ];
        }

        return $points;
    }

    private static function color(string $mode, string $label, int $index): string
    {
        if ($mode === 'status') {
            $key = strtolower(preg_replace('/[\s_-]+/', '', $label) ?? $label);
            if (str_contains($key, 'pending') || str_contains($key, 'submitted')) {
                return '#F59E0B';
            }
            if (str_contains($key, 'progress') || str_contains($key, 'investigat')) {
                return '#3B82F6';
            }
            if (str_contains($key, 'resolved') || str_contains($key, 'closed') || str_contains($key, 'decision')) {
                return '#1D9E75';
            }
            if (str_contains($key, 'dismiss')) {
                return '#DC6B6B';
            }

            return '#7A8F82';
        }

        if ($mode === 'sky') {
            return '#7DCEB3';
        }

        return self::CATEGORY[$index % count(self::CATEGORY)];
    }

    private static function open(int $width, int $height, string $label): string
    {
        return '<svg viewBox="0 0 '.$width.' '.$height.'" class="h-full w-full" role="img" aria-label="'.e($label).'" xmlns="http://www.w3.org/2000/svg">';
    }

    private static function grid(array $ticks, int $yMax, float $left, float $top, float $plotW, float $plotH, float $right): string
    {
        $svg = '';
        foreach ($ticks as $tick) {
            $y = $top + $plotH - ($tick / $yMax) * $plotH;
            $svg .= '<line x1="'.$left.'" y1="'.self::n($y).'" x2="'.$right.'" y2="'.self::n($y).'" stroke="#e5e7eb" stroke-dasharray="4 4" />';
            $svg .= self::label((string) $tick, $left - 8, $y + 4, 'end');
        }

        return $svg;
    }

    private static function roundedBar(float $x, float $y, float $w, float $h, float $r, string $color, string $tip): string
    {
        if ($h <= 0) {
            return '';
        }
        $r = min($r, $w / 2, $h);
        $d = 'M '.self::n($x).' '.self::n($y + $r)
            .' Q '.self::n($x).' '.self::n($y).' '.self::n($x + $r).' '.self::n($y)
            .' L '.self::n($x + $w - $r).' '.self::n($y)
            .' Q '.self::n($x + $w).' '.self::n($y).' '.self::n($x + $w).' '.self::n($y + $r)
            .' L '.self::n($x + $w).' '.self::n($y + $h)
            .' L '.self::n($x).' '.self::n($y + $h).' Z';

        return '<path d="'.$d.'" fill="'.$color.'"><title>'.$tip.'</title></path>';
    }

    private static function smooth(array $coords): string
    {
        $first = $coords[0];
        $d = 'M '.self::n($first[0]).' '.self::n($first[1]);
        $last = count($coords) - 1;
        for ($i = 0; $i < $last; $i++) {
            [$x1, $y1] = $coords[$i];
            [$x2, $y2] = $coords[$i + 1];
            $dx = ($x2 - $x1) * 0.42;
            $d .= ' C '.self::n($x1 + $dx).' '.self::n($y1).', '.self::n($x2 - $dx).' '.self::n($y2).', '.self::n($x2).' '.self::n($y2);
        }

        return $d;
    }

    private static function ticks(int $yMax): array
    {
        if ($yMax <= 4) {
            return range(0, $yMax);
        }
        $step = (int) max(1, ceil($yMax / 4));
        $ticks = [];
        for ($value = 0; $value <= $yMax; $value += $step) {
            $ticks[] = $value;
        }
        if (end($ticks) !== $yMax) {
            $ticks[] = $yMax;
        }

        return $ticks;
    }

    private static function niceMax(int $max): int
    {
        return max(1, $max);
    }

    private static function label(string $text, float $x, float $y, string $anchor, string $fill = '#6b7280'): string
    {
        return '<text x="'.self::n($x).'" y="'.self::n($y).'" text-anchor="'.$anchor.'" fill="'.$fill.'" font-size="11" font-family="Sora, ui-sans-serif, sans-serif">'.e($text).'</text>';
    }

    private static function short(string $label): string
    {
        return mb_strlen($label) > 22 ? mb_substr($label, 0, 20).'…' : $label;
    }

    private static function n(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
