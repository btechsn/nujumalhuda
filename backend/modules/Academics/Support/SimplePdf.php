<?php

declare(strict_types=1);

namespace Modules\Academics\Support;

final class SimplePdf
{
    /**
     * @param  list<string>  $lines
     */
    public static function write(string $absolutePath, array $lines): void
    {
        \Modules\Core\Support\SimplePdf::write($absolutePath, $lines);
    }
}
