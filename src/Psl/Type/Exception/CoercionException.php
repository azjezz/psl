<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Str;
use Psl\Vec;
use Throwable;

use function get_debug_type;

final class CoercionException extends Exception
{
    private string $target;

    /**
     * @param list<string> $paths
     */
    private function __construct(string $actual, string $target, array $paths = [], null|Throwable $previous = null)
    {
        $first = ($previous instanceof Exception) ? $previous->getFirstFailingActualType() : $actual;

        parent::__construct(
            Str\format(
                'Could not coerce "%s" to type "%s"%s%s.',
                $first,
                $target,
                $paths ? (' at path "' . Str\join($paths, '.') . '"') : '',
                $previous && !($previous instanceof self) ? (': ' . $previous->getMessage()) : '',
            ),
            $actual,
            $paths,
            $previous,
        );

        $this->target = $target;
    }

    public function getTargetType(): string
    {
        return $this->target;
    }

    public static function withValue(
        mixed $value,
        string $target,
        null|string $path = null,
        null|Throwable $previous = null,
    ): self {
        $paths = ($previous instanceof Exception) ? [$path, ...$previous->getPaths()] : [$path];

        return new self(get_debug_type($value), $target, Vec\filter_nulls($paths), $previous);
    }
}
