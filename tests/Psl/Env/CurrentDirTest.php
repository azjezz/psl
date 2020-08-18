<?php

declare(strict_types=1);

namespace Psl\Tests\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;

class CurrentDirTest extends TestCase
{
    public function testCurrentDir(): void
    {
        self::assertSame(getcwd(), Env\current_dir());

        Env\set_current_dir(__DIR__);

        self::assertSame(__DIR__, Env\current_dir());
    }
}
