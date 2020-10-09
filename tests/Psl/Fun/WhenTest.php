<?php

declare(strict_types=1);

namespace Psl\Tests\Fun;

use PHPUnit\Framework\TestCase;
use Psl\Fun;

final class WhenTest extends TestCase
{
    public function testItRunsLeftFunction(): void
    {
        $greet = Fun\when(
            static fn(string $name): bool => $name === 'Jos',
            static fn(string $name): string => 'Bonjour ' . $name . '!',
            static fn(string $name): string => 'Hello ' . $name . '!'
        );

        self::assertSame('Bonjour Jos!', $greet('Jos'));
    }

    public function testItRunsRightfunction(): void
    {
        $greet = Fun\when(
            static fn(string $name): bool => $name === 'Jos',
            static fn(string $name): string => 'Bonjour ' . $name . '!',
            static fn(string $name): string => 'Hello ' . $name . '!'
        );

        self::assertSame('Hello World!', $greet('World'));
    }
}
