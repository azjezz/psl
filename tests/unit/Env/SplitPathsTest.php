<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\Str;

final class SplitPathsTest extends TestCase
{
    public function testSplitPaths(): void
    {
        static::assertSame(['/home/azjezz', '/tmp'], Env\split_paths(Str\format('/home/azjezz%s/tmp', PATH_SEPARATOR)));
        static::assertSame(['/home/azjezz', '/tmp'], Env\split_paths(Env\join_paths('/home/azjezz', '/tmp')));
        static::assertSame(['/home/azjezz'], Env\split_paths('/home/azjezz'));
    }
}
