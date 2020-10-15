<?php

declare(strict_types=1);

namespace Psl\Tests\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;

final class GetVarsTest extends TestCase
{
    /**
     * @backupGlobals
     */
    public function testGetVars(): void
    {
        $expected = getenv();

        static::assertSame($expected, Env\get_vars());

        Env\set_var('FOO', 'BAR');

        static::assertNotSame($expected, Env\get_vars());
        static::assertSame(getenv(), Env\get_vars());

        Env\remove_var('FOO');
    }
}
