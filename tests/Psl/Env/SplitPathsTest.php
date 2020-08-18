<?php

declare(strict_types=1);

namespace Psl\Tests\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\Str;

class SplitPathsTest extends TestCase
{
    public function testSplitPaths(): void
    {
        self::assertSame(['/home/azjezz', '/tmp'], Env\split_paths(Str\format('/home/azjezz%s/tmp', PATH_SEPARATOR)));
        self::assertSame(['/home/azjezz', '/tmp'], Env\split_paths(Env\join_paths('/home/azjezz', '/tmp')));
        self::assertSame(['/home/azjezz'], Env\split_paths('/home/azjezz'));
    }
}
