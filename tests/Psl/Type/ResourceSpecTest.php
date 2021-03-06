<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Type;

final class ResourceSpecTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\resource('stream');
    }

    public function getValidCoercions(): iterable
    {
        yield [STDIN, STDIN];
        yield [STDOUT, STDOUT];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [null];
        yield ['hello'];
        yield ['https://void.tn'];
        yield [__FILE__];
    }

    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'resource<stream>'];
        yield [Type\resource('curl'), 'resource<curl>'];
        yield [Type\resource(), 'resource'];
    }

    public function testCurlResourceDisallowsStream(): void
    {
        $spec = Type\resource('curl');

        $this->expectException(Type\Exception\AssertException::class);

        $spec->assert(STDIN);
    }

    public function testNoKind(): void
    {
        $spec = Type\resource();

        $value = $spec->assert(STDIN);
        static::assertSame(STDIN, $value);

        $value = $spec->coerce(STDIN);
        static::assertSame(STDIN, $value);
    }
}
