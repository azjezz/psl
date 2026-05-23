<?php

declare(strict_types=1);

namespace Psl\Regex\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Regex;

final class CaptureGroupsTest extends TestCase
{
    public function testItAlwaysAddsZeroCaptureResult(): void
    {
        $data = [0 => 'Hello', 1 => 'World'];
        $shape = Regex\capture_groups([1]);
        $actual = $shape->coerce($data);

        static::assertSame($actual, $data);
    }
}
