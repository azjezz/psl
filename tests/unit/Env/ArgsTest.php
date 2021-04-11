<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;

final class ArgsTest extends TestCase
{
    public function testArgs(): void
    {
        static::assertSame($GLOBALS['argv'], Env\args());
    }
}
