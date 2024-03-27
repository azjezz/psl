<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Exception\RuntimeException;

abstract class Exception extends RuntimeException implements ExceptionInterface
{
    private string $actual;

    public function __construct(
        string $message,
        string $actual,
    ) {
        parent::__construct($message);

        $this->actual    = $actual;
    }

    public function getActualType(): string
    {
        return $this->actual;
    }
}
