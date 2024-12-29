<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

use Psl\Exception\RuntimeException;
use Throwable;

abstract class Exception extends RuntimeException implements ExceptionInterface
{
    private string $actual;

    /**
     * @var list<string>
     */
    private array $paths;

    private string $first;

    /**
     * @param list<string> $paths
     */
    protected function __construct(string $message, string $actual, array $paths, null|Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->paths = $paths;
        $this->first = ($previous instanceof self) ? $previous->first : $actual;
        $this->actual = $actual;
    }

    /**
     * @return list<string>
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getActualType(): string
    {
        return $this->actual;
    }

    public function getFirstFailingActualType(): string
    {
        return $this->first;
    }
}
