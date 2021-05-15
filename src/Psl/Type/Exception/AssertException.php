<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Str;

final class AssertException extends Exception
{
    private string $expected;

    public function __construct(string $actual, string $expected, TypeTrace $typeTrace)
    {
        parent::__construct(Str\format('Expected "%s", got "%s".', $expected, $actual), $actual, $typeTrace);

        $this->expected = $expected;
    }

    public function getExpectedType(): string
    {
        return $this->expected;
    }

    public static function withValue(
        mixed $value,
        string $expected_type,
        TypeTrace $trace
    ): self {
        return new self(self::getDebugType($value), $expected_type, $trace);
    }
}
