<?php

declare(strict_types=1);

namespace Psl\Tests\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;

class GetVarsTest extends TestCase
{
    /**
     * @backupGlobals
     */
    public function testGetVars(): void
    {
        $expected = getenv();

        self::assertSame($expected, Env\get_vars());

        Env\set_var('FOO', 'BAR');

        self::assertNotSame($expected, Env\get_vars());
        self::assertSame(getenv(), Env\get_vars());

        Env\remove_var('FOO');
    }
}
