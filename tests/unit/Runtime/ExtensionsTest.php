<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Runtime;

use PHPUnit\Framework\TestCase;
use Psl\Runtime;

final class ExtensionsTest extends TestCase
{
    public function testExtensions(): void
    {
        foreach (Runtime\get_extensions() as $extension) {
            static::assertTrue(Runtime\has_extension($extension));
        }

        foreach (Runtime\get_zend_extensions() as $zend_extension) {
            static::assertTrue(Runtime\has_extension($zend_extension));
        }

        static::assertFalse(Runtime\has_extension('php-standard-library'));
    }
}
