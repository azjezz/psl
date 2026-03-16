<?php

declare(strict_types=1);

namespace Psl\File\Exception;

use function sprintf;

final class NotFileException extends InvalidArgumentException
{
    public static function for(string $path): NotFileException
    {
        return new self(sprintf('Path "%s" does not point to a file.', $path));
    }
}
