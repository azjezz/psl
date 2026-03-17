<?php

declare(strict_types=1);

namespace Psl\Filesystem\Exception;

use function sprintf;

final class NotFoundException extends InvalidArgumentException
{
    public static function forNode(string $node): NotFoundException
    {
        return new self(sprintf('Node "%s" is not found.', $node));
    }

    public static function forFile(string $file): NotFoundException
    {
        return new self(sprintf('File "%s" is not found.', $file));
    }

    public static function forDirectory(string $directory): NotFoundException
    {
        return new self(sprintf('Directory "%s" is not found.', $directory));
    }

    public static function forSymbolicLink(string $symbolicLink): NotFoundException
    {
        return new self(sprintf('Symbolic link "%s" is not found.', $symbolicLink));
    }
}
