<?php

declare(strict_types=1);

namespace Psl\Env\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Env;

use function sprintf;

final class JoinPathsTest extends TestCase
{
    public function testJoinPaths(): void
    {
        static::assertSame(sprintf('/home/azjezz%s/tmp', PATH_SEPARATOR), Env\join_paths('/home/azjezz', '/tmp'));
        static::assertSame('/home/azjezz', Env\join_paths('/home/azjezz'));
    }
}
