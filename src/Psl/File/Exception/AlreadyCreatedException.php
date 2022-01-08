<?php

declare(strict_types=1);

namespace Psl\File\Exception;

use Psl\Str;

final class AlreadyCreatedException extends RuntimeException
{
    public static function for(string $path): AlreadyCreatedException
    {
        return new self(Str\format('File "%s" is already created.', $path));
    }
}
