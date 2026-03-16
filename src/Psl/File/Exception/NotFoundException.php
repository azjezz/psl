<?php

declare(strict_types=1);

namespace Psl\File\Exception;

use function sprintf;

final class NotFoundException extends InvalidArgumentException
{
    public static function for(string $file): NotFoundException
    {
        return new self(sprintf('File "%s" is not found.', $file));
    }
}
