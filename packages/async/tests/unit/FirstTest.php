<?php

declare(strict_types=1);

namespace Psl\Async\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime;

final class FirstTest extends TestCase
{
    public function testFirst(): void
    {
        $result = Async\first::<string>([
            Async\run::<string>(static function (): string {
                Async\sleep(DateTime\Duration::milliseconds(1));

                return 'a';
            }),
            Async\run::<string>(static function (): string {
                Async\sleep(DateTime\Duration::milliseconds(2));

                return 'b';
            }),
            Async\run::<string>(static function (): string {
                Async\sleep(DateTime\Duration::milliseconds(3));

                return 'c';
            }),
            Async\run::<string>(static function (): string {
                Async\sleep(DateTime\Duration::milliseconds(5));

                Async\later();

                Async\sleep(DateTime\Duration::milliseconds(5));

                return 'c';
            }),
        ]);

        static::assertSame('a', $result);
    }

    public function testFirstWithNoArguments(): void
    {
        $this->expectException(Async\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('$awaitables must be a non-empty-iterable.');

        Async\first::<mixed>([]);
    }
}
