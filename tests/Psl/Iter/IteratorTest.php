<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;
use Psl\Iter;

class IteratorTest extends TestCase
{
    public function testSeek(): void
    {
        $iterator = new Iter\Iterator([1, 2, 3, 4, 5]);

        $this->assertSame(1, $iterator->current());
        $iterator->next();
        $this->assertSame(2, $iterator->current());
        $iterator->next();

        $iterator->seek(0);
        $this->assertSame(1, $iterator->current());
    }

    public function testSeekThrowsForOutOfBoundIndex(): void
    {
        $iterator = new Iter\Iterator([1, 2, 3, 4, 5]);

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Position (30) is out-of-bounds.');

        $iterator->seek(30);
    }
}
