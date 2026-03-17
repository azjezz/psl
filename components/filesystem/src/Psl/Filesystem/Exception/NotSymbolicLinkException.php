<?php

declare(strict_types=1);

namespace Psl\Filesystem\Exception;

use function sprintf;

final class NotSymbolicLinkException extends InvalidArgumentException
{
    public static function for(string $path): NotSymbolicLinkException
    {
        return new self(sprintf('Path "%s" does not point to a symbolic link.', $path));
    }
}
