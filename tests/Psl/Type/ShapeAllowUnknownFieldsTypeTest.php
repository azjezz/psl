<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use Psl\Collection;
use Psl\Iter;
use Psl\Type;

/**
 * @extends TypeTest<array>
 */
final class ShapeAllowUnknownFieldsTypeTest extends TypeTest
{
    public function getType(): Type\TypeInterface
    {
        return Type\shape([
            'name' => Type\string(),
            'articles' => Type\vec(Type\shape([
                'title' => Type\string(),
                'content' => Type\string(),
                'likes' => Type\int(),
                'comments' => Type\optional(Type\vec(Type\shape([
                    'user' => Type\string(),
                    'comment' => Type\string()
                ]))),
            ]))
        ], true);
    }

    public function getValidCoercions(): iterable
    {
        yield [
            ['name' => 'saif', 'articles' => new Collection\Vector([])],
            ['name' => 'saif', 'articles' => []]
        ];

        yield [
            ['name' => 'saif', 'email' => 'azjezz@example.com', 'articles' => new Collection\Vector([
                ['title' => 'Foo', 'content' => 'Baz', 'likes' => 0],
            ])],

            // unknown fields are always last.
            ['name' => 'saif', 'articles' => [
                ['title' => 'Foo', 'content' => 'Baz', 'likes' => 0],
            ],  'email' => 'azjezz@example.com']
        ];

        yield [
            ['name' => 'saif', 'articles' => new Collection\Vector([
                ['title' => 'Foo', 'content' => 'Baz', 'likes' => 0],
            ])],
            ['name' => 'saif', 'articles' => [
                ['title' => 'Foo', 'content' => 'Baz', 'likes' => 0],
            ]]
        ];

        yield [
            ['name' => 'saif', 'articles' => new Collection\Vector([
                ['title' => 'Foo', 'content' => 'Baz', 'likes' => 0],
                ['title' => 'Bar', 'content' => 'Qux', 'likes' => 0, 'comments' => [
                    ['user' => 'a', 'comment' => 'hello'],
                    ['user' => 'b', 'comment' => 'hey'],
                    ['user' => 'c', 'comment' => 'hi'],
                ]],
            ])],
            ['name' => 'saif', 'articles' => [
                ['title' => 'Foo', 'content' => 'Baz', 'likes' => 0],
                ['title' => 'Bar', 'content' => 'Qux', 'likes' => 0, 'comments' => [
                    ['user' => 'a', 'comment' => 'hello'],
                    ['user' => 'b', 'comment' => 'hey'],
                    ['user' => 'c', 'comment' => 'hi'],
                ]],
            ]],
        ];

        yield [
            ['name' => 'saif', 'articles' => new Collection\Vector([
                ['title' => 'Foo', 'content' => 'Bar', 'likes' => 0],
                ['title' => 'Baz', 'content' => 'Qux', 'likes' => 13],
            ])],
            ['name' => 'saif', 'articles' => [
                ['title' => 'Foo', 'content' => 'Bar', 'likes' => 0],
                ['title' => 'Baz', 'content' => 'Qux', 'likes' => 13],
            ]],
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
            "array{'name': string, 'articles': list<array{" .
                "'title': string, " .
                "'content': string, " .
                "'likes': int, " .
                "'comments'?: list<array{'user': string, 'comment': string}>" .
            "}>}"
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
        $dict = Type\dict(Type\array_key(), Type\mixed());
        if (!$dict->matches($a) || !$dict->matches($b)) {
            return parent::equals($a, $b);
        }

        if (!Iter\contains_key($a, 'articles') || !Iter\contains_key($b, 'articles')) {
            return parent::equals($a, $b);
        }

        $vector = Type\object(Collection\VectorInterface::class);
        if ($vector->matches($a['articles'])) {
            $a['articles'] = $a['articles']->toArray();
        }

        if ($vector->matches($b['articles'])) {
            $b['articles'] = $b['articles']->toArray();
        }

        return parent::equals($a, $b);
    }
}
