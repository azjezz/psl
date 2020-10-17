<?php

declare(strict_types=1);

namespace Psl\Tests\Hash\Hmac;

use PHPUnit\Framework\TestCase;
use Psl\Hash\Hmac;

use function hash_hmac_algos;

final class AlgorithmsTest extends TestCase
{
    public function testAlgorithms(): void
    {
        static::assertSame(hash_hmac_algos(), Hmac\algorithms());
    }
}
