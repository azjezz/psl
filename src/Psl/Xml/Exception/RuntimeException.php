<?php

declare(strict_types=1);

namespace Psl\Xml\Exception;

use Exception;
use Psl\Exception\RuntimeException as PslRuntimeException;
use Psl\Xml\Issue\IssueCollection;

final class RuntimeException extends PslRuntimeException implements ExceptionInterface
{
    private function __construct(string $message, Exception $previous = null)
    {
        parent::__construct(
            $message,
            (int) ($previous ? $previous->getCode() : 0),
            $previous
        );
    }

    public static function fromIssues(string $message, IssueCollection $errors): self
    {
        return new self($message . PHP_EOL . $errors->toString());
    }

    public static function combineExceptionWithIssues(Exception $exception, IssueCollection $errors): self
    {
        return new self(
            $exception->getMessage() . PHP_EOL . $errors->toString(),
            $exception
        );
    }
}
