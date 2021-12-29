<?php

declare(strict_types=1);

namespace Psl\Filesystem\Exception;

use Psl\Str;

final class NotFileException extends InvalidArgumentException
{
    public static function for(string $path): NotFileException
    {
        return new self(Str\format('Path "%s" does not point to a file.', $path));
    }
}
