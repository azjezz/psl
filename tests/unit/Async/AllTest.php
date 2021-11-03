<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;
use Psl\Exception\InvariantViolationException;

final class AllTest extends TestCase
{
    public function testAll(): void
    {
        $awaitables = [
            'a' => Async\run(static function (): string {
                Async\usleep(100);

                return 'a';
            }),
            'b' => Async\run(static function (): string {
                Async\usleep(100);

                return 'b';
            }),
            'c' => Async\run(static function (): string {
                Async\usleep(100);

                return 'c';
            }),
        ];

        $results = Async\all($awaitables);

        static::assertSame(['a' => 'a', 'b' => 'b', 'c' => 'c'], $results);
    }

    public function testAllForwardsException(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('a');

        Async\all([
            Async\run(static function (): string {
                Async\usleep(100);

                throw new InvariantViolationException('a');
            }),
            Async\run(static function (): string {
                Async\usleep(200);

                throw new InvariantViolationException('b');
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
    }

    public function testAllAwaitablesAreCompleted(): void
    {
        $ref = new Psl\Ref('');

        try {
            Async\all([
                Async\run(static function () use ($ref): void {
                    $ref->value .= 'a';

                    throw new InvariantViolationException('a');
                }),
                Async\run(static function () use ($ref): void {
                    Async\usleep(2000);

                    $ref->value .= 'b';

                    throw new InvariantViolationException('b');
                }),
                Async\run(static function () use ($ref): void {
                    Async\usleep(3000);

                    $ref->value .= 'c';
                }),
                Async\run(static function () use ($ref): void {
                    Async\usleep(500);

                    Async\later();

                    Async\usleep(500);

                    $ref->value .= 'd';
                }),
            ]);
        } catch (InvariantViolationException) {
        }

        static::assertSame('adbc', $ref->value);
    }
}
