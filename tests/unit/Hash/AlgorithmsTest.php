<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Hash;

use PHPUnit\Framework\TestCase;
use Psl\Hash;

use function hash_algos;

final class AlgorithmsTest extends TestCase
{
    public function testAlgorithms(): void
    {
        static::assertSame(hash_algos(), Hash\algorithms());
    }
}
