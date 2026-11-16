<?php

namespace App\Support;

use App\Models\Entry;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Draws a cover for an entry that has no uploaded picture: a bold poster made
 * of flat shapes, with the title set beside or beneath it.
 *
 * The look is worked out from the entry's slug, so the same entry always gets
 * the same cover. It can be pinned with "motif:palette" in cover_design.
 */
class CoverArt
{
    private const INK = '#17151c';

    private const PAPER = '#fff7e8';

    /** name => [background, shape, accent] */
    public const PALETTES = [
        'tomato' => ['#ff5c39', '#ffcf33', '#fff7e8'],
        'sun' => ['#ffcf33', '#ff5c39', '#17151c'],
        'mint' => ['#2fd1a5', '#fff7e8', '#ff5c39'],
        'cobalt' => ['#3d6bff', '#ffcf33', '#fff7e8'],
        'grape' => ['#7c5cff', '#2fd1a5', '#ffcf33'],
        'ink' => ['#17151c', '#ff5c39', '#ffcf33'],
        'paper' => ['#fff7e8', '#3d6bff', '#ff5c39'],
        'forest' => ['#1f7a63', '#ffcf33', '#fff7e8'],
        'sky' => ['#9bdcff', '#3d6bff', '#ff5c39'],
        'brick' => ['#c9432b', '#fff7e8', '#ffcf33'],
    ];

    public const MOTIFS = ['sun', 'stripes', 'dots', 'waves', 'arcs', 'blocks', 'peaks', 'burst', 'rings'];

    private static int $drawn = 0;

    private int $random;

    private function __construct(int $seed)
    {
        $this->random = $seed ?: 1;
    }

    /** @return array{0: string, 1: string} motif and palette names */
    public static function design(Entry $entry): array
    {
        $seed = crc32((string) ($entry->slug ?: $entry->title));
        [$motif, $palette] = array_pad(explode(':', (string) $entry->cover_design), 2, null);

        return [
            in_array($motif, self::MOTIFS, true) ? $motif : self::MOTIFS[$seed % count(self::MOTIFS)],
            isset(self::PALETTES[$palette]) ? $palette : array_keys(self::PALETTES)[intdiv($seed, 16) % count(self::PALETTES)],
        ];
    }

    public static function render(Entry $entry): HtmlString
    {
        [$motif, $palette] = self::design($entry);
        [$background, $shape, $accent] = self::PALETTES[$palette];
        $seed = crc32((string) ($entry->slug ?: $entry->title));
        $artist = new self($seed);

        // Tall for books, square for audio, wide for video and articles.
        [$w, $h] = match ($entry->type->coverShape()) {
            'tall' => [300, 450],
            'square' => [300, 300],
            default => [480, 270],
        };

        // The picture sits on top (or on the left of a wide cover); the words get the rest.
        if ($w > $h) {
            $art = [0, 0, 190, $h];
            $panel = [190, 0, $w - 190, $h];
        } else {
            $artHeight = (int) round($h * ($h > $w ? 0.58 : 0.5));
            $art = [0, 0, $w, $artHeight];
            $panel = [0, $artHeight, $w, $h - $artHeight];
        }

        $darkPanel = $palette === 'paper' || ($palette !== 'ink' && $seed % 3 === 0);
        $panelFill = $darkPanel ? self::INK : self::PAPER;
        $panelText = $darkPanel ? self::PAPER : self::INK;

        // Words: the title as big as it will go, the creator along the bottom.
        $pad = 20;
        $textWidth = $panel[2] - $pad * 2;
        [$size, $lines] = self::fit((string) $entry->title, $textWidth, $panel[3] - 62, match (true) {
            $w > $h => 32, $w === $h => 40, default => 46
        });

        $text = '';
        $baseline = $panel[1] + 22 + $size * 0.82;
        foreach ($lines as $line) {
            $text .= sprintf('<text x="%d" y="%s" font-size="%d">%s</text>', $panel[0] + $pad, self::n($baseline), $size, e($line));
            $baseline += $size * 1.08;
        }

        $creator = Str::limit(mb_strtoupper((string) $entry->creator), max(8, (int) floor($textWidth / 8.6)), '…');

        $id = 'cover-'.dechex($seed).'-'.(++self::$drawn);
        $label = e('Cover of '.$entry->title.' by '.$entry->creator);
        $divider = $w > $h
            ? sprintf('<line x1="%1$d" y1="0" x2="%1$d" y2="%2$d"/>', $panel[0], $h)
            : sprintf('<line x1="0" y1="%1$d" x2="%2$d" y2="%1$d"/>', $panel[1], $w);
        $spine = $h > $w ? sprintf('<rect width="11" height="%d" fill="%s" opacity=".17"/>', $h, self::INK) : '';

        $picture = $artist->{$motif}($art, $background, $shape, $accent);
        $creatorX = $panel[0] + $pad;
        $creatorY = $panel[1] + $panel[3] - 17;
        $creatorText = e($creator);

        $svg = <<<SVG
<svg class="cover-art" viewBox="0 0 {$w} {$h}" role="img" aria-label="{$label}" xmlns="http://www.w3.org/2000/svg">
<defs><clipPath id="{$id}"><rect x="{$art[0]}" y="{$art[1]}" width="{$art[2]}" height="{$art[3]}"/></clipPath></defs>
<rect width="{$w}" height="{$h}" fill="{$background}"/>
<g clip-path="url(#{$id})">{$picture}</g>
<rect x="{$panel[0]}" y="{$panel[1]}" width="{$panel[2]}" height="{$panel[3]}" fill="{$panelFill}"/>
<g stroke="#17151c" stroke-width="4">{$divider}</g>
{$spine}
<g fill="{$panelText}" font-family="'Fraunces Variable', Georgia, serif" font-weight="800" letter-spacing="-.4">{$text}</g>
<text x="{$creatorX}" y="{$creatorY}" fill="{$panelText}" font-family="'DM Sans Variable', system-ui, sans-serif" font-weight="700" font-size="11" letter-spacing="1.3">{$creatorText}</text>
</svg>
SVG;

        return new HtmlString($svg);
    }

    /* ------------------------------------------------------------------ words */

    /**
     * The biggest size at which the title fits the panel, and how it breaks into lines.
     *
     * @return array{0: int, 1: list<string>}
     */
    private static function fit(string $title, float $width, float $height, int $largest): array
    {
        $words = preg_split('/\s+/u', trim($title), -1, PREG_SPLIT_NO_EMPTY) ?: ['Untitled'];
        $lines = [];

        for ($size = $largest; $size >= 15; $size -= 1) {
            $perLine = max(5, (int) floor($width / ($size * 0.6)));   // a letter is roughly 0.6 of the type size wide
            $maxLines = max(1, (int) floor($height / ($size * 1.08)));

            $lines = [];
            $line = '';
            foreach ($words as $word) {
                $try = $line === '' ? $word : $line.' '.$word;
                if (mb_strlen($try) <= $perLine) {
                    $line = $try;
                } else {
                    if ($line !== '') {
                        $lines[] = $line;
                    }
                    $line = $word;
                }
            }
            $lines[] = $line;

            if (count($lines) <= $maxLines && max(array_map('mb_strlen', $lines)) <= $perLine) {
                return [$size, $lines];
            }
        }

        // Still too long at the smallest size: keep what fits and trail off.
        $perLine = max(5, (int) floor($width / (15 * 0.6)));
        $maxLines = max(1, (int) floor($height / (15 * 1.08)));
        $lines = array_slice(array_map(fn (string $line) => Str::limit($line, $perLine - 1, '…'), $lines), 0, $maxLines);
        $lines[count($lines) - 1] = Str::limit($lines[count($lines) - 1], $perLine - 2, '…');

        return [15, $lines];
    }

    /* ------------------------------------------------------------------ pictures */
    // Each takes the box to fill [x, y, width, height] and three colours.

    private function sun(array $box, string $bg, string $shape, string $accent): string
    {
        [$x, $y, $w, $h] = $box;
        $cx = $x + $w * 0.5;
        $cy = $y + $h * 0.6;
        $r = min($w, $h) * 0.29;

        $rays = '';
        for ($i = 0; $i < 14; $i++) {
            $angle = deg2rad($i * (360 / 14) - 90);
            $far = $r * ($i % 2 ? 1.62 : 1.9);
            $rays .= self::line($cx + cos($angle) * $r * 1.28, $cy + sin($angle) * $r * 1.28, $cx + cos($angle) * $far, $cy + sin($angle) * $far);
        }

        return '<g stroke="'.$accent.'" stroke-width="7" stroke-linecap="round">'.$rays.'</g>'
            .'<circle cx="'.self::n($cx).'" cy="'.self::n($cy).'" r="'.self::n($r).'" fill="'.$shape.'" stroke="'.self::INK.'" stroke-width="3.5"/>'
            .'<rect x="'.$x.'" y="'.self::n($y + $h * 0.8).'" width="'.$w.'" height="'.self::n($h * 0.2 + 2).'" fill="'.$accent.'" stroke="'.self::INK.'" stroke-width="3.5"/>';
    }

    private function stripes(array $box, string $bg, string $shape, string $accent): string
    {
        [$x, $y, $w, $h] = $box;
        $cx = $x + $w / 2;
        $cy = $y + $h / 2;
        $step = 27;

        $bars = '';
        for ($i = -14; $i <= 14; $i += 2) {
            $bars .= '<rect x="'.self::n($cx + $i * $step).'" y="'.self::n($cy - 420).'" width="'.$step.'" height="840"/>';
        }

        $r = min($w, $h) * 0.2;

        return '<g fill="'.$shape.'" transform="rotate(-32 '.self::n($cx).' '.self::n($cy).')">'.$bars.'</g>'
            .'<circle cx="'.self::n($x + $w * 0.68).'" cy="'.self::n($y + $h * 0.36).'" r="'.self::n($r).'" fill="'.$accent.'" stroke="'.self::INK.'" stroke-width="3.5"/>';
    }

    private function dots(array $box, string $bg, string $shape, string $accent): string
    {
        [$x, $y, $w, $h] = $box;
        $step = 25;
        $rows = (int) ceil($h / $step) + 1;
        $cols = (int) ceil($w / $step) + 1;

        // Comic-book halftone: the dots shrink as they go down the page.
        $dots = '';
        for ($row = 0; $row < $rows; $row++) {
            $radius = 11.5 * (1 - $row / $rows) + 1.2;
            for ($col = 0; $col <= $cols; $col++) {
                $dots .= '<circle cx="'.self::n($x + $col * $step + ($row % 2 ? $step / 2 : 0)).'" cy="'.self::n($y + 6 + $row * $step).'" r="'.self::n($radius).'"/>';
            }
        }

        $r = min($w, $h) * 0.23;

        return '<g fill="'.$shape.'">'.$dots.'</g>'
            .'<circle cx="'.self::n($x + $w * 0.36).'" cy="'.self::n($y + $h * 0.68).'" r="'.self::n($r).'" fill="'.$accent.'" stroke="'.self::INK.'" stroke-width="3.5"/>';
    }

    private function waves(array $box, string $bg, string $shape, string $accent): string
    {
        [$x, $y, $w, $h] = $box;
        $half = $w / 6;   // half a wavelength
        $out = '<circle cx="'.self::n($x + $w * 0.72).'" cy="'.self::n($y + $h * 0.2).'" r="'.self::n(min($w, $h) * 0.12).'" fill="'.$accent.'" stroke="'.self::INK.'" stroke-width="3.5"/>';

        for ($k = 0; $k < 4; $k++) {
            $base = $y + $h * (0.36 + 0.17 * $k);
            $path = 'M'.self::n($x - $half * ($k % 2)).' '.self::n($base);
            for ($i = 0; $i < 8; $i++) {
                $path .= ' q'.self::n($half / 2).' '.($i % 2 ? 26 : -26).' '.self::n($half).' 0';
            }
            $path .= ' V'.($y + $h + 4).' H'.($x - $half).' Z';
            $out .= '<path d="'.$path.'" fill="'.($k % 2 ? $accent : $shape).'" stroke="'.self::INK.'" stroke-width="3.5"/>';
        }

        return $out;
    }

    private function arcs(array $box, string $bg, string $shape, string $accent): string
    {
        [$x, $y, $w, $h] = $box;
        $fills = [$shape, $bg, $accent, $bg, $shape, $accent];
        $largest = max($w, $h) * 1.04;

        $out = '';
        foreach ($fills as $i => $fill) {
            $out .= '<circle cx="'.$x.'" cy="'.($y + $h).'" r="'.self::n($largest * (1 - $i / 6.4)).'" fill="'.$fill.'" stroke="'.self::INK.'" stroke-width="3.5"/>';
        }

        return $out;
    }

    private function blocks(array $box, string $bg, string $shape, string $accent): string
    {
        [$x, $y, $w, $h] = $box;
        $cols = $w >= 280 ? 3 : 2;
        $s = $w / $cols;
        $rows = (int) ceil($h / $s);

        $out = '';
        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                $left = $x + $col * $s;
                $top = $y + $row * $s;
                $fill = ($row + $col) % 2 ? $accent : $shape;
                $corner = $this->pick(4);

                $out .= match ($this->pick(5)) {
                    0 => '<circle cx="'.self::n($left + $s / 2).'" cy="'.self::n($top + $s / 2).'" r="'.self::n($s * 0.36).'" fill="'.$fill.'"/>',
                    1 => '<rect x="'.self::n($left + $s * 0.18).'" y="'.self::n($top + $s * 0.18).'" width="'.self::n($s * 0.64).'" height="'.self::n($s * 0.64).'" fill="'.$fill.'"/>',
                    2 => '<path d="M'.self::n($left).' '.self::n($top + $s).' L'.self::n($left + $s / 2).' '.self::n($top + $s * 0.12).' L'.self::n($left + $s).' '.self::n($top + $s).' Z" fill="'.$fill.'"/>',
                    default => '<path d="'.self::quarter($left, $top, $s, $corner).'" fill="'.$fill.'"/>',
                };
            }
        }

        return '<g stroke="'.self::INK.'" stroke-width="3" stroke-linejoin="round">'.$out.'</g>';
    }

    private function peaks(array $box, string $bg, string $shape, string $accent): string
    {
        [$x, $y, $w, $h] = $box;
        $out = '<circle cx="'.self::n($x + $w * 0.26).'" cy="'.self::n($y + $h * 0.24).'" r="'.self::n(min($w, $h) * 0.13).'" fill="'.$accent.'" stroke="'.self::INK.'" stroke-width="3.5"/>';

        foreach ([$shape, $accent, $shape] as $k => $fill) {
            $count = 2 + $k;
            $base = $y + $h * (0.62 + 0.17 * $k);
            $points = self::n($x - 4).','.self::n($base);

            for ($i = 0; $i < $count; $i++) {
                $height = $h * (0.36 - 0.07 * $k) * (0.7 + $this->pick(30) / 100);
                $points .= ' '.self::n($x + ($i + 0.5) * $w / $count).','.self::n($base - $height).' '.self::n($x + ($i + 1) * $w / $count + ($i === $count - 1 ? 4 : 0)).','.self::n($base);
            }

            $points .= ' '.($x + $w + 4).','.($y + $h + 4).' '.($x - 4).','.($y + $h + 4);
            $out .= '<polygon points="'.$points.'" fill="'.$fill.'" stroke="'.self::INK.'" stroke-width="3.5" stroke-linejoin="round"/>';
        }

        return $out;
    }

    private function burst(array $box, string $bg, string $shape, string $accent): string
    {
        [$x, $y, $w, $h] = $box;
        $cx = $x + $w / 2;
        $cy = $y + $h / 2;
        $far = hypot($w, $h);

        // Rays to the edges, like the panel behind a comic-book "POW".
        $rays = '';
        for ($i = 0; $i < 20; $i += 2) {
            $a = deg2rad($i * 18 - 6);
            $b = deg2rad(($i + 1) * 18 - 6);
            $rays .= '<path d="M'.self::n($cx).' '.self::n($cy).' L'.self::n($cx + cos($a) * $far).' '.self::n($cy + sin($a) * $far).' L'.self::n($cx + cos($b) * $far).' '.self::n($cy + sin($b) * $far).' Z"/>';
        }

        $outer = min($w, $h) * 0.34;
        $star = '';
        for ($i = 0; $i < 24; $i++) {
            $angle = deg2rad($i * 15 - 90);
            $radius = $i % 2 ? $outer * 0.68 : $outer;
            $star .= self::n($cx + cos($angle) * $radius).','.self::n($cy + sin($angle) * $radius).' ';
        }

        return '<g fill="'.$shape.'">'.$rays.'</g>'
            .'<polygon points="'.trim($star).'" fill="'.$accent.'" stroke="'.self::INK.'" stroke-width="3.5" stroke-linejoin="round"/>';
    }

    private function rings(array $box, string $bg, string $shape, string $accent): string
    {
        [$x, $y, $w, $h] = $box;
        $cx = $x + $w / 2;
        $cy = $y + $h / 2;
        $unit = max($w, $h) * 0.105;

        $out = '';
        for ($i = 6; $i >= 1; $i--) {
            $out .= '<circle cx="'.self::n($cx).'" cy="'.self::n($cy).'" r="'.self::n($unit * $i).'" fill="'.($i % 2 ? $shape : $bg).'" stroke="'.self::INK.'" stroke-width="3.5"/>';
        }

        return $out.'<circle cx="'.self::n($cx).'" cy="'.self::n($cy).'" r="'.self::n($unit * 0.42).'" fill="'.$accent.'" stroke="'.self::INK.'" stroke-width="3.5"/>';
    }

    /* ------------------------------------------------------------------ helpers */

    /** A quarter circle filling a square cell, rounded towards one of its four corners. */
    private static function quarter(float $left, float $top, float $s, int $corner): string
    {
        [$ax, $ay, $bx, $by, $cx, $cy, $sweep] = match ($corner) {
            0 => [$left, $top, $left + $s, $top, $left, $top + $s, 1],
            1 => [$left + $s, $top, $left + $s, $top + $s, $left, $top, 1],
            2 => [$left + $s, $top + $s, $left, $top + $s, $left + $s, $top, 1],
            default => [$left, $top + $s, $left, $top, $left + $s, $top + $s, 1],
        };

        return 'M'.self::n($ax).' '.self::n($ay).' L'.self::n($bx).' '.self::n($by).' A'.self::n($s).' '.self::n($s).' 0 0 '.$sweep.' '.self::n($cx).' '.self::n($cy).' Z';
    }

    private static function line(float $x1, float $y1, float $x2, float $y2): string
    {
        return '<line x1="'.self::n($x1).'" y1="'.self::n($y1).'" x2="'.self::n($x2).'" y2="'.self::n($y2).'"/>';
    }

    /** A number as short as SVG needs it. */
    private static function n(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
    }

    /** The next number from 0 to $below - 1: always the same run for the same entry. */
    private function pick(int $below): int
    {
        $this->random = ($this->random * 1103515245 + 12345) & 0x7FFFFFFF;

        return intdiv($this->random, 65536) % $below;
    }
}
