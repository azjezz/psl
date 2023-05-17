<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Str;
use Throwable;

use function get_debug_type;

final class CoercionException extends Exception
{
    private string $target;

    public function __construct(string $actual, string $target, TypeTrace $typeTrace, string $additionalInfo = '')
    {
        parent::__construct(
            Str\format(
                'Could not coerce "%s" to type "%s"%s%s',
                $actual,
                $target,
                $additionalInfo ? ': ' : '.',
                $additionalInfo
            ),
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

    public static function withConversionFailureOnValue(
        mixed $value,
        string $target,
        TypeTrace $typeTrace,
        Throwable $failure,
    ): self {
        return new self(
            get_debug_type($value),
            $target,
            $typeTrace,
            $failure->getMessage()
        );
    }
}
