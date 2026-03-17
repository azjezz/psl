<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Throwable;

use function array_filter;
use function array_values;
use function get_debug_type;
use function implode;
use function sprintf;

final class AssertException extends Exception
{
    private string $expected;

    /**
     * @param list<string> $paths
     */
    private function __construct(string $actual, string $expected, array $paths = [], null|Throwable $previous = null)
    {
        $first = $previous instanceof Exception ? $previous->getFirstFailingActualType() : $actual;

        parent::__construct(
            sprintf(
                'Expected "%s", got "%s"%s.',
                $expected,
                $first,
                $paths ? ' at path "' . implode('.', $paths) . '"' : '',
            ),
            $actual,
            $paths,
            $previous,
        );

        $this->expected = $expected;
    }

    public function getExpectedType(): string
    {
        return $this->expected;
    }

    public static function withValue(
        mixed $value,
        string $expectedType,
        null|string $path = null,
        null|Throwable $previous = null,
    ): self {
        $paths = $previous instanceof Exception ? [$path, ...$previous->getPaths()] : [$path];
        /** @var list<string> $paths */
        $paths = array_values(array_filter($paths, static fn($v) => $v !== null));

        return new self(get_debug_type($value), $expectedType, $paths, $previous);
    }
}
