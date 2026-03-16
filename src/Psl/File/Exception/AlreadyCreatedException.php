<?php

declare(strict_types=1);

namespace Psl\File\Exception;

use function sprintf;

final class AlreadyCreatedException extends RuntimeException
{
    public static function for(string $path): AlreadyCreatedException
    {
        return new self(sprintf('File "%s" is already created.', $path));
    }
}
