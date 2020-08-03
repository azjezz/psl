<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Str;

final class TypeAssertException extends TypeException
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

    /**
     * @param mixed $value
     */
    public static function withValue(
        $value,
        string $expected_type,
        TypeTrace $trace
    ): self {
        return new self(static::getDebugType($value), $expected_type, $trace);
    }
}
