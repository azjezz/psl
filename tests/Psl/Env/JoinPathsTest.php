<?php

declare(strict_types=1);

namespace Psl\Tests\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\Str;

class JoinPathsTest extends TestCase
{
    public function testJoinPaths(): void
    {
        self::assertSame(Str\format('/home/azjezz%s/tmp', PATH_SEPARATOR), Env\join_paths('/home/azjezz', '/tmp'));
        self::assertSame('/home/azjezz', Env\join_paths('/home/azjezz'));
    }
}
