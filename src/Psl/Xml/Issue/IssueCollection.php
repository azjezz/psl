<?php

declare(strict_types=1);

namespace Psl\Xml\Issue;

use Countable;
use IteratorAggregate;
use Psl\Arr;
use Psl\Iter\Iterator;
use Psl\Math;
use Psl\Str;

final class IssueCollection implements Countable, IteratorAggregate
{
    /**
     * @var list<Issue>
     */
    private array $issues;

    public function __construct(Issue ...$errors)
    {
        $this->issues = $errors;
    }

    /**
     * @return Iterator<int, Issue>
     */
    public function getIterator(): Iterator
    {
        return Iterator::create($this->issues);
    }

    public function count(): int
    {
        return count($this->issues);
    }

    /**
     * @param (callable(Issue): bool) $filter
     */
    public function filter(callable $filter): self
    {
        return new self(...Arr\filter($this->issues, $filter));
    }

    public function getHighestLevel(): ?Level
    {
        $issue = Math\max_by($this->issues, static fn(Issue $issue): int => $issue->level()->value());

        return $issue ? $issue->level() : null;
    }

    public function toString(): string
    {
        $values = Arr\values(Arr\map($this->issues, static fn (Issue $error): string => $error->toString()));

        return Str\join($values, PHP_EOL);
    }
}
