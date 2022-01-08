<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl\Async;

final class FirstTest extends TestCase
{
    public function testFirst(): void
    {
        $result = Async\first([
            Async\run(static function (): string {
                Async\sleep(0.001);

                return 'a';
            }),
            Async\run(static function (): string {
                Async\sleep(0.002);

                return 'b';
            }),
            Async\run(static function (): string {
                Async\sleep(0.003);

                return 'c';
            }),
            Async\run(static function (): string {
                Async\sleep(0.0005);

                Async\later();

                Async\sleep(0.0005);

                return 'c';
            }),
        ]);

        static::assertSame('a', $result);
    }

    public function testFirstWithNoArguments(): void
    {
        $this->expectException(Async\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$awaitables must be a non-empty-iterable.');

        Async\first([]);
    }
}
