<?php

declare(strict_types=1);

namespace Psl\URI\Exception;

final class InvalidTemplateException extends InvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function forUnclosedExpression(int $position): self
    {
        return new self('Unclosed template expression at position ' . $position . '.');
    }

    public static function forInvalidOperator(string $operator): self
    {
        return new self('Invalid template operator "' . $operator . '".');
    }

    public static function forInvalidModifier(string $modifier): self
    {
        return new self('Invalid template modifier "' . $modifier . '".');
    }

    public static function forEmptyVariableName(): self
    {
        return new self('Template variable name must not be empty.');
    }
}
