<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Fixture;

enum StringEnum: string
{
    case Foo = 'foo';
    case Bar = '1';
    case Baz = 'baz';
}
