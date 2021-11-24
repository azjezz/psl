<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\Filesystem;

final class CurrentExecTest extends TestCase
{
    public function testCurrentExe(): void
    {
        $executable = Env\current_exec();

        static::assertSame('phpunit', Filesystem\get_basename($executable));
    }
}
