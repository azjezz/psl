<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Str;

use function get_debug_type;

final class CoercionException extends Exception
{
    private string $target;

    public function __construct(string $actual, string $target, TypeTrace $typeTrace)
    {
        parent::__construct(
            Str\format('Could not coerce "%s" to type "%s".', $actual, $target),
            $actual,
            $typeTrace,
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
        TypeTrace $typeTrace
    ): self {
        return new self(get_debug_type($value), $target, $typeTrace);
    }
}
