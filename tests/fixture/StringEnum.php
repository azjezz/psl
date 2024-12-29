<?php

declare(strict_types=1);

namespace Psl\Tests\Fixture;

enum StringEnum: string
{
    case Foo = 'foo';
    case Bar = '1';
    case Baz = 'baz';
}
