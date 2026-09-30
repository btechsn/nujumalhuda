<?php

declare(strict_types=1);

namespace Modules\Core\Support;

final class SimplePdf
{
    /**
     * Écrit un PDF une page en Helvetica. Les caractères hors Latin-1 sont translittérés.
     *
     * @param  list<string>  $lines
     */
    public static function write(string $absolutePath, array $lines): void
    {
        $directory = dirname($absolutePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $commands = ["BT", "/F1 16 Tf", "50 780 Td", "18 TL"];
        foreach (array_values($lines) as $index => $line) {
            if ($index === 1) {
                $commands[] = "/F1 12 Tf";
            }
            $commands[] = '(' . self::escape($line) . ') Tj';
            $commands[] = 'T*';
        }
        $commands[] = 'ET';
        $stream = implode("\n", $commands);

        $objects = [
            "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n",
            "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n",
            "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n",
            "4 0 obj << /Length " . strlen($stream) . " >> stream\n" . $stream . "\nendstream endobj\n",
            "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        file_put_contents($absolutePath, $pdf);
    }

    private static function escape(string $line): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $line);
        $ascii = $ascii === false ? ' ' : $ascii;
        $ascii = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $ascii);

        return preg_replace('/[^\x20-\x7E]/', ' ', $ascii) ?? ' ';
    }
}
