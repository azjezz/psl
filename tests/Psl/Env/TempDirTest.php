<?php

declare(strict_types=1);

namespace Psl\Tests\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;

class TempDirTest extends TestCase
{
    public function testTempDir(): void
    {
        static::assertSame(sys_get_temp_dir(), Env\temp_dir());
    }
}
