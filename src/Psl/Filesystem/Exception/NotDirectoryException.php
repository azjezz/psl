<?php

declare(strict_types=1);

namespace Psl\Filesystem\Exception;

use Psl\Str;

final class NotDirectoryException extends InvalidArgumentException
{
    public static function for(string $path): NotDirectoryException
    {
        return new self(Str\format('Path "%s" does not point to a directory.', $path));
    }
}
