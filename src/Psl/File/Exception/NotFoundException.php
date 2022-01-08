<?php

declare(strict_types=1);

namespace Psl\File\Exception;

use Psl\Str;

final class NotFoundException extends InvalidArgumentException
{
    public static function for(string $file): NotFoundException
    {
        return new self(Str\format('File "%s" is not found.', $file));
    }
}
