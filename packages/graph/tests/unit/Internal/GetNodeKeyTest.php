<?php

declare(strict_types=1);

namespace Psl\Graph\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Graph\Internal;
use stdClass;

final class GetNodeKeyTest extends TestCase
{
    public function testObject(): void
    {
        $obj = new stdClass();
        $key = Internal\get_node_key($obj);

        static::assertStringStartsWith('o:', $key);
    }

    public function testArray(): void
    {
        $key = Internal\get_node_key(['a', 'b']);

        static::assertStringStartsWith('a:', $key);
    }

    public function testResource(): void
    {
        $key = Internal\get_node_key(STDIN);

        static::assertStringStartsWith('r:', $key);
    }

    public function testBoolTrue(): void
    {
        static::assertSame('b:1', Internal\get_node_key(true));
    }

    public function testBoolFalse(): void
    {
        static::assertSame('b:0', Internal\get_node_key(false));
    }

    public function testInt(): void
    {
        static::assertSame('i:42', Internal\get_node_key(42));
    }

    public function testFloat(): void
    {
        static::assertSame('f:3.14', Internal\get_node_key(3.14));
    }

    public function testString(): void
    {
        static::assertSame('s:hello', Internal\get_node_key('hello'));
    }

    public function testNull(): void
    {
        static::assertSame('n:null', Internal\get_node_key(null));
    }
}
