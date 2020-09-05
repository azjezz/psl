<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Exception\RuntimeException;
use Psl\Str;

abstract class TypeException extends RuntimeException
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

    /**
     * @param mixed $value
     */
    protected static function getDebugType($value): string
    {
        if (is_object($value)) {
            $actual_type = get_class($value);
        } elseif (is_resource($value)) {
            $actual_type = Str\format('resource<%s>', get_resource_type($value));
        } elseif (is_int($value)) {
            $actual_type = 'int';
        } elseif (is_float($value)) {
            $actual_type = 'float';
        } else {
            $actual_type = gettype($value);
        }

        return $actual_type;
    }
}
