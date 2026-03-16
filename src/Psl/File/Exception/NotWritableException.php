<?php

declare(strict_types=1);

namespace Psl\File\Exception;

use function sprintf;

final class NotWritableException extends InvalidArgumentException
{
    public static function for(string $file): NotWritableException
    {
        return new self(sprintf('File "%s" is not writable.', $file));
    }
}
