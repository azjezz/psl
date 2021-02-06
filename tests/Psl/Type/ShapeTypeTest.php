<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Arr;
use Psl\Collection;
use Psl\Type;

/**
 * @extends TypeTest<array>
 */
final class ShapeTypeTest extends TypeTest
{
    public function getType(): Type\Type
    {
        return Type\shape([
            'name' => Type\string(),
            'articles' => Type\vector(Type\shape([
                'title' => Type\string(),
                'content' => Type\string(),
                'likes' => Type\int()
            ]))
        ]);
    }

    public function getValidCoercions(): iterable
    {
        yield [
            ['name' => 'saif', 'articles' => []],
            ['name' => 'saif', 'articles' => new Collection\Vector([])]
        ];

        yield [
            ['name' => 'saif', 'articles' => [
                ['title' => 'Foo', 'content' => 'Baz', 'likes' => 0],
            ]],
            ['name' => 'saif', 'articles' => new Collection\Vector([
                ['title' => 'Foo', 'content' => 'Baz', 'likes' => 0],
            ])]
        ];

        yield [
            ['name' => 'saif', 'articles' => [
                ['title' => 'Foo', 'content' => 'Bar', 'likes' => 0],
                ['title' => 'Baz', 'content' => 'Qux', 'likes' => 13],
            ]],
            ['name' => 'saif', 'articles' => new Collection\Vector([
                ['title' => 'Foo', 'content' => 'Bar', 'likes' => 0],
                ['title' => 'Baz', 'content' => 'Qux', 'likes' => 13],
            ])]
        ];
    }

    public function getInvalidCoercions(): iterable
    {
        yield [1.0];
        yield [1.23];
        yield [Type\bool()];
        yield [null];
        yield [false];
        yield [true];
        yield [STDIN];
        yield [['name' => 'saif', 'articles' => 5]];
        yield [['name' => 'saif', 'baz' => []]];
        yield [['name' => 'saif', 'baz' => []]];
        yield [['name' => 'saif', 'articles' => [
            ['title' => 'biz'] // missing 'content' and 'likes'
        ]]];
        yield [['name' => 'saif', 'articles' => [
            ['title' => 'biz', 'content' => 'foo', 'upvotes'] // 'likes' replaced by 'upvotes'
        ]]];
    }

    public function getToStringExamples(): iterable
    {
        yield [
            $this->getType(),
            'array{\'name\': string, \'articles\': Psl\Collection\VectorInterface<' .
                'array{\'title\': string, \'content\': string, \'likes\': int}' .
            '>}'
        ];
        yield [
            Type\shape([Type\int(), Type\string()]),
            'array{0: int, 1: string}'
        ];
    }

    /**
     * @psalm-param Collection\VectorInterface<mixed>|mixed $a
     * @psalm-param Collection\VectorInterface<mixed>|mixed $b
     */
    protected function equals($a, $b): bool
    {
        if (!Type\is_array($a) || !Type\is_array($b)) {
            return parent::equals($a, $b);
        }

        if (!Arr\contains_key($a, 'articles') || !Arr\contains_key($b, 'articles')) {
            return parent::equals($a, $b);
        }

        if (Type\is_object($a['articles']) && Type\is_instanceof($a['articles'], Collection\VectorInterface::class)) {
            $a['articles'] = $a['articles']->toArray();
        }

        if (Type\is_object($b['articles']) && Type\is_instanceof($b['articles'], Collection\VectorInterface::class)) {
            $b['articles'] = $b['articles']->toArray();
        }

        return parent::equals($a, $b);
    }
}
