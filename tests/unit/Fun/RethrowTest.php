<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Fun;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Fun;

final class RethrowTest extends TestCase
{
    public function testRethrow(): void
    {
        $exception = new Exception('foo');
        $rethrow = Fun\rethrow();

        $this->expectExceptionObject($exception);
        $rethrow($exception);
    }
}
