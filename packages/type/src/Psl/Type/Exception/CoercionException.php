<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Throwable;

use function array_filter;
use function array_values;
use function get_debug_type;
use function implode;
use function sprintf;

final class CoercionException extends Exception
{
    private string $target;

    /**
     * @param list<string> $paths
     */
    private function __construct(string $actual, string $target, array $paths = [], null|Throwable $previous = null)
    {
        $first = $previous instanceof Exception ? $previous->getFirstFailingActualType() : $actual;

        parent::__construct(
            sprintf(
                'Could not coerce "%s" to type "%s"%s%s.',
                $first,
                $target,
                $paths ? ' at path "' . implode('.', $paths) . '"' : '',
                $previous && !$previous instanceof self ? ': ' . $previous->getMessage() : '',
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
        $paths = $previous instanceof Exception ? [$path, ...$previous->getPaths()] : [$path];
        /** @var list<string> $paths */
        $paths = array_values(array_filter($paths, static fn($v) => $v !== null));

        return new self(get_debug_type($value), $target, $paths, $previous);
    }
}
