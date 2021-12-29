<?php

declare(strict_types=1);

namespace Psl\Filesystem\Exception;

use Psl\Str;

final class NotFoundException extends InvalidArgumentException
{
    public static function forNode(string $node): NotFoundException
    {
        return new self(Str\format('Node "%s" is not found.', $node));
    }

    public static function forFile(string $file): NotFoundException
    {
        return new self(Str\format('File "%s" is not found.', $file));
    }

    public static function forDirectory(string $directory): NotFoundException
    {
        return new self(Str\format('Directory "%s" is not found.', $directory));
    }

    public static function forSymbolicLink(string $symbolic_link): NotFoundException
    {
        return new self(Str\format('Symbolic link "%s" is not found.', $symbolic_link));
    }
}
