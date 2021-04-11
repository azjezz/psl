<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

use function array_keys;

final class KeysTest extends TestCase
{
    public function testValues(): void
    {
        $array = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ];

        $keys = Arr\keys($array);

        static::assertSameSize($keys, $array);
        static::assertSame($keys, array_keys($array));

        foreach ($array as $key => $value) {
            static::assertTrue(Arr\contains($keys, $key));
        }
    }
}
