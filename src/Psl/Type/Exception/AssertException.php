<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Str;

use function get_debug_type;

final class AssertException extends Exception
{
    private string $expected;

    public function __construct(string $actual, string $expected)
    {
        parent::__construct(Str\format('Expected "%s", got "%s".', $expected, $actual), $actual);

        $this->expected = $expected;
    }

    public function getExpectedType(): string
    {
        return $this->expected;
    }

    public static function withValue(
        mixed $value,
        string $expected_type,
    ): self {
        return new self(get_debug_type($value), $expected_type);
    }
}
