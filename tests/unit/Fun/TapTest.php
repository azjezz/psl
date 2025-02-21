<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Fun;

use PHPUnit\Framework\TestCase;
use Psl\Fun;
use Psl\Hash;
use Psl\Ref;
use Psl\Str;

final class TapTest extends TestCase
{
    public function testItWorksAsACurriedFunctionThatCanBeUsedForPerformingSideEffects(): void
    {
        $log = new Ref('123');
        $call = Fun\tap(static function (string $x) use ($log): void {
            $log->value .= $x;
        });

        static::assertSame('abc', $call('abc'));
        static::assertSame('123abc', $log->value);

        static::assertSame('def', $call('def'));
        static::assertSame('123abcdef', $log->value);
    }

    public function testItCanBeCombinedInOtherFlowsForDebugging(): void
    {
        $log = new Ref('');
        $result = Fun\pipe(
            static fn(string $x): string => Hash\hash($x, Hash\Algorithm::Md5),
            Fun\tap(static function (string $x) use ($log): void {
                $log->value = $x;
            }),
            static fn(string $x): string => Str\truncate($x, 0, 1),
        )('abc');

        $md5 = Hash\hash('abc', Hash\Algorithm::Md5);
        $firstChar = Str\truncate($md5, 0, 1);

        static::assertSame($firstChar, $result);
        static::assertSame($md5, $log->value);
    }
}
