<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Regex;

use PHPUnit\Framework\TestCase;

use function Psl\Regex\capture_groups;

final class CaptureGroupsTest extends TestCase
{
    public function testItAlwaysAddsZeroCaptureResult(): void
    {
        $data = [0 => 'Hello', 1 => 'World'];
        $shape = capture_groups([1]);
        $actual = $shape->coerce($data);

        static::assertSame($actual, $data);
    }
}
