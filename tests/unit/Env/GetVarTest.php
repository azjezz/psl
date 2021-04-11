<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;

final class GetVarTest extends TestCase
{
    /**
     * @backupGlobals
     */
    public function testGetVar(): void
    {
        static::assertNull(Env\get_var('FOO'));

        Env\set_var('FOO', 'BAR');

        static::assertSame('BAR', Env\get_var('FOO'));

        Env\remove_var('FOO');
    }
}
