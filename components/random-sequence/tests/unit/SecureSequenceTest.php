<?php

declare(strict_types=1);

namespace Psl\RandomSequence\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\RandomSequence\SecureSequence;

final class SecureSequenceTest extends TestCase
{
    public function testNext(): void
    {
        $sequence = SecureSequence::default();

        $a = $sequence->next();
        $b = $sequence->next();

        static::assertNotSame($a, $b);
    }
}
