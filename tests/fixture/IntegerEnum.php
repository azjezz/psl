<?php

declare(strict_types=1);

namespace Psl\Tests\Fixture;

enum IntegerEnum: int
{
    case Foo = 1;
    case Bar = 2;
    case Baz = -3;
}
