<?php

declare(strict_types=1);

namespace Psl\Tests\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;

class GetVarTest extends TestCase
{
    /**
     * @backupGlobals
     */
    public function testGetVar(): void
    {
        self::assertNull(Env\get_var('FOO'));

        Env\set_var('FOO', 'BAR');

        self::assertSame('BAR', Env\get_var('FOO'));

        Env\remove_var('FOO');
    }
}
