<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\OS;

use PHPUnit\Framework\TestCase;
use Psl\OS;

use const PHP_OS_FAMILY;

final class FamilyTest extends TestCase
{
    public function testFamily(): void
    {
        if (OS\is_windows()) {
            static::assertSame(OS\OperatingSystemFamily::Windows, OS\family());
            static::assertFalse(OS\is_darwin());
        } elseif (OS\is_darwin()) {
            static::assertSame(OS\OperatingSystemFamily::Darwin, OS\family());
            static::assertFalse(OS\is_windows());
        } else {
            static::assertNotSame(OS\OperatingSystemFamily::Windows, OS\family());
            static::assertNotSame(OS\OperatingSystemFamily::Darwin, OS\family());
            static::assertFalse(OS\is_windows());
            static::assertFalse(OS\is_darwin());
        }

        static::assertSame(PHP_OS_FAMILY, OS\family()->value);
    }
}
