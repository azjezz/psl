<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Str;
use Psl\Vec;
use Throwable;

use function get_debug_type;

final class AssertException extends Exception
{
    private string $expected;

    /**
     * @param list<string> $paths
     */
    private function __construct(string $actual, string $expected, array $paths = [], null|Throwable $previous = null)
    {
        $first = ($previous instanceof Exception) ? $previous->getFirstFailingActualType() : $actual;

        parent::__construct(
            Str\format(
                'Expected "%s", got "%s"%s.',
                $expected,
                $first,
                $paths ? (' at path "' . Str\join($paths, '.') . '"') : '',
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
        string $expected_type,
        null|string $path = null,
        null|Throwable $previous = null,
    ): self {
        $paths = ($previous instanceof Exception) ? [$path, ...$previous->getPaths()] : [$path];

        return new self(get_debug_type($value), $expected_type, Vec\filter_nulls($paths), $previous);
    }
}
