<?php

declare(strict_types=1);

namespace Psl\Filesystem\Exception;

use Psl\Str;

final class NotSymbolicLinkException extends InvalidArgumentException
{
    public static function for(string $path): NotSymbolicLinkException
    {
        return new self(Str\format('Path "%s" does not point to a symbolic link.', $path));
    }
}
