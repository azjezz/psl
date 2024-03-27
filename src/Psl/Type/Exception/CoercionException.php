<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Str;
use Throwable;

use function get_debug_type;

final class CoercionException extends Exception
{
    private string $target;

    public function __construct(string $actual, string $target, string $additionalInfo = '')
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
    ): self {
        return new self(get_debug_type($value), $target);
    }

    public static function withConversionFailureOnValue(
        mixed $value,
        string $target,
        Throwable $failure,
    ): self {
        return new self(
            get_debug_type($value),
            $target,
            $failure->getMessage()
        );
    }
}
