<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Unit;

use Override;
use Psl\Type;

final class ResourceTypeTest extends TypeTestCase
{
    #[Override]
    public static function getType(): Type\TypeInterface
    {
        return Type\resource('stream');
    }

    #[Override]
    public static function getValidCoercions(): iterable
    {
        yield [STDIN, STDIN];
        yield [STDOUT, STDOUT];
    }

    #[Override]
    public static function getInvalidCoercions(): iterable
    {
        yield [null];
        yield ['hello'];
        yield ['https://void.tn'];
        yield [__FILE__];
    }

    #[Override]
    public static function getToStringExamples(): iterable
    {
        yield [static::getType(), 'resource (stream)'];
        yield [Type\resource('curl'), 'resource (curl)'];
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
