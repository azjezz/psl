<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Exception\InvariantViolationException;

final class FirstTest extends TestCase
{
    public function testFirst(): void
    {
        $result = Async\first([
            Async\run(static function (): string {
                Async\usleep(100);

                return 'a';
            }),
            Async\run(static function (): string {
                Async\usleep(200);

                return 'b';
            }),
            Async\run(static function (): string {
                Async\usleep(300);

                return 'c';
            }),
            Async\run(static function (): string {
                Async\usleep(50);

                Async\later();

                Async\usleep(50);

                return 'c';
            }),
        ]);

        static::assertSame('a', $result);
    }

    public function testFirstWithNoArguments(): void
    {
        $this->expectException(InvariantViolationException::class);

        Async\first([]);
    }
}
