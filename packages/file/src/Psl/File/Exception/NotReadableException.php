<?php

declare(strict_types=1);

namespace Psl\File\Exception;

use function sprintf;

final class NotReadableException extends InvalidArgumentException
{
    public static function for(string $file): NotReadableException
    {
        return new self(sprintf('File "%s" is not readable.', $file));
    }
}
