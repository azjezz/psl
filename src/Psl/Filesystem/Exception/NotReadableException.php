<?php

declare(strict_types=1);

namespace Psl\Filesystem\Exception;

use Psl\Str;

final class NotReadableException extends InvalidArgumentException
{
    public static function forFile(string $file): NotReadableException
    {
        return new self(Str\format('File "%s" is not readable.', $file));
    }

    public static function forDirectory(string $directory): NotReadableException
    {
        return new self(Str\format('Directory "%s" is not readable.', $directory));
    }
}
