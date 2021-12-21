<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Exception\RuntimeException;

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
}
