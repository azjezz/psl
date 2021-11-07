<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Runtime;

use PHPUnit\Framework\TestCase;
use Psl\Runtime;

use function zend_version;

final class ZendTest extends TestCase
{
    public function testGetZendVersion(): void
    {
        static::assertSame(zend_version(), Runtime\get_zend_version());
    }
}
