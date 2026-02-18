<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type\Internal;

use Psl\Tests\Unit\Type\TypeTestCase;
use Psl\Type;

use const STDIN;

/**
 * @extends TypeTestCase<non-empty-string>
 */
final class UuidTypeTest extends TypeTestCase
{
    /**
     * @return Type\Type<non-empty-string>
     */
    #[\Override]
    public function getType(): Type\TypeInterface
    {
        return Type\uuid();
    }

    #[\Override]
    public function getValidCoercions(): iterable
    {
        yield ['2E58B54C-ADE0-41CD-A806-90420571991B', '2E58B54C-ADE0-41CD-A806-90420571991B'];
        yield [$this->stringable('3E5AF91A-D381-4996-94DB-16DB3B6B20F7'), '3E5AF91A-D381-4996-94DB-16DB3B6B20F7'];
        yield ['abf9bb28-14c0-48eb-be4f-e7b2b2203c8f', 'abf9bb28-14c0-48eb-be4f-e7b2b2203c8f'];
        yield [$this->stringable('88b82321-6993-4e94-961f-52e093153fae'), '88b82321-6993-4e94-961f-52e093153fae'];
    }

    #[\Override]
    public function getInvalidCoercions(): iterable
    {
        yield [''];
        yield [1.0];
        yield [1.23];
        yield [[]];
        yield [[1]];
        yield [Type\bool()];
        yield [null];
        yield [false];
        yield [true];
        yield [STDIN];
        yield ['kermit'];
        yield ['88b82321-6993-4e94-961f-52e093153fa'];
        yield ['E58B54C-ADE0-41CD-A806-90420571991B'];
    }

    #[\Override]
    public function getToStringExamples(): iterable
    {
        yield [$this->getType(), 'uuid'];
    }

    public function testItIsAMemoizedType(): void
    {
        static::assertSame(Type\non_empty_string(), Type\non_empty_string());
    }
}
