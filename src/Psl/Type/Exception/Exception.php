<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Exception\RuntimeException;
use Psl\Str;

use function get_debug_type;

abstract class Exception extends RuntimeException implements ExceptionInterface
{
    private TypeTrace $typeTrace;
    private string $actual;

    public function __construct(
        string $message,
        string $actual,
        TypeTrace $typeTrace
    ) {
        parent::__construct($message);

        $this->actual    = $actual;
        $this->typeTrace = $typeTrace;
    }

    public function getActualType(): string
    {
        return $this->actual;
    }

    public function getTypeTrace(): TypeTrace
    {
        return $this->typeTrace;
    }

    protected static function getDebugType(mixed $value): string
    {
        if (is_resource($value)) {
            return Str\format('resource<%s>', get_resource_type($value));
        }

        return get_debug_type($value);
    }
}
