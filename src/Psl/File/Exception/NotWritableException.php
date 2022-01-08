<?php

declare(strict_types=1);

namespace Psl\File\Exception;

use Psl\Str;

final class NotWritableException extends InvalidArgumentException
{
    public static function for(string $file): NotWritableException
    {
        return new self(Str\format('File "%s" is not writable.', $file));
    }
}
