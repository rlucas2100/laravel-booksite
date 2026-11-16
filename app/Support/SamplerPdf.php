<?php

namespace App\Support;

/**
 * Writes a small one-page PDF by hand: a "sampler" card with a book's opening
 * lines. The seeder uses it so every book on the shelves has a real file to
 * download, without the project carrying a pile of PDFs or a PDF library.
 */
class SamplerPdf
{
    private const WIDTH = 420;    // A5 in points

    private const HEIGHT = 595;

    private const MARGIN = 46;

    /** The bytes of the PDF. */
    public static function make(string $shop, string $title, string $author, string $opening, ?string $link = null): string
    {
        $usable = self::WIDTH - self::MARGIN * 2;
        $y = self::HEIGHT - 28;

        // A band of colour across the top with the shop's name in it.
        $page = '1 0.36 0.22 rg 0 '.(self::HEIGHT - 46).' '.self::WIDTH." 46 re f\n";
        $page .= self::text('F4', 9, self::MARGIN, $y, mb_strtoupper($shop.' sampler'), [0.09, 0.08, 0.11], 1.6);

        $y = self::HEIGHT - 96;
        foreach (self::wrap($title, 26, 0.54, $usable) as $line) {
            $page .= self::text('F2', 26, self::MARGIN, $y, $line);
            $y -= 31;
        }

        $y -= 2;
        $page .= self::text('F3', 14, self::MARGIN, $y, 'by '.$author);

        $y -= 22;
        $page .= '0.09 0.08 0.11 RG 2 w '.self::MARGIN." $y m ".(self::MARGIN + 64)." $y l S\n";

        $y -= 30;
        $page .= self::text('F4', 8.5, self::MARGIN, $y, 'HOW IT BEGINS', [0.37, 0.35, 0.4], 1.6);

        $y -= 26;
        foreach (preg_split('/\R/u', trim($opening)) as $paragraph) {
            foreach (self::wrap($paragraph, 13.5, 0.47, $usable) as $line) {
                $page .= self::text('F1', 13.5, self::MARGIN, $y, $line);
                $y -= 20;
            }
            $y -= 4;
        }

        // Footer.
        $footer = 74;
        if ($link) {
            $page .= self::text('F4', 9, self::MARGIN, $footer, 'Read the whole book free at Project Gutenberg:', [0.37, 0.35, 0.4]);
            $page .= self::text('F4', 9, self::MARGIN, $footer - 14, $link, [0.24, 0.42, 1]);
        }
        $page .= self::text('F4', 8, self::MARGIN, 40, 'This book is in the public domain. Sampler made by '.$shop.'.', [0.37, 0.35, 0.4]);

        return self::assemble($page);
    }

    /** Break text into lines that fit. $ratio is the average letter width as a share of the type size. */
    private static function wrap(string $text, float $size, float $ratio, float $width): array
    {
        return explode("\n", wordwrap(trim(preg_replace('/\s+/u', ' ', $text)), max(10, (int) floor($width / ($size * $ratio))), "\n", true));
    }

    /** One line of text. PDF's built-in fonts speak Windows-1252, so the text is converted and escaped. */
    private static function text(string $font, float $size, float $x, float $y, string $text, array $rgb = [0.09, 0.08, 0.11], float $spacing = 0): string
    {
        $text = mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);

        return sprintf("BT %s %s %s rg /%s %s Tf %s Tc %s %s Td (%s) Tj ET\n", $rgb[0], $rgb[1], $rgb[2], $font, $size, $spacing, $x, $y, $text);
    }

    /** Wrap the page in the objects, cross-reference table and trailer a PDF reader expects. */
    private static function assemble(string $page): string
    {
        $font = fn (string $name) => "<< /Type /Font /Subtype /Type1 /BaseFont /$name /Encoding /WinAnsiEncoding >>";

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.self::WIDTH.' '.self::HEIGHT.'] /Contents 8 0 R '
                .'/Resources << /Font << /F1 4 0 R /F2 5 0 R /F3 6 0 R /F4 7 0 R >> >> >>',
            4 => $font('Times-Roman'),
            5 => $font('Times-Bold'),
            6 => $font('Times-Italic'),
            7 => $font('Helvetica-Bold'),
            8 => '<< /Length '.strlen($page)." >>\nstream\n".$page.'endstream',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "$number 0 obj\n$body\nendobj\n";
        }

        $start = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n$start\n%%EOF\n";
    }
}
