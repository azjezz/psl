<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

class KeysTest extends TestCase
{
    public function testValues(): void
    {
        $array = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ];

        $keys = Arr\keys($array);

        self::assertSameSize($keys, $array);
        self::assertSame($keys, \array_keys($array));

        foreach ($array as $key => $value) {
            self::assertTrue(Arr\contains($keys, $key));
        }
    }
}
