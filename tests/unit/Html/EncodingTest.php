<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Html;

use PHPUnit\Framework\TestCase;
use Psl\Html;

final class EncodingTest extends TestCase
{
    public function testDefault(): void
    {
        static::assertSame(Html\Encoding::Utf8, Html\Encoding::default());
    }
}
