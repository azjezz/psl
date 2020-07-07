<?php

declare(strict_types=1);

namespace Psl\Tests;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Exception\InvariantViolationException;

class InvariantTest extends TestCase
{
    public function testInvariant(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Something went wrong!');

        Psl\invariant(false, 'Something %s %s!', 'went', 'wrong');
    }

    public function testInvariantDoesNotThrowWhenTheFactIsTrue(): void
    {
        Psl\invariant(2 === (1 + 1), 'Unless?');

        $this->addToAssertionCount(1);
    }
}
