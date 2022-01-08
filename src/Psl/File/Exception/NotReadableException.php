<?php

declare(strict_types=1);

namespace Psl\File\Exception;

use Psl\Str;

final class NotReadableException extends InvalidArgumentException
{
    public static function for(string $file): NotReadableException
    {
        return new self(Str\format('File "%s" is not readable.', $file));
    }
}
