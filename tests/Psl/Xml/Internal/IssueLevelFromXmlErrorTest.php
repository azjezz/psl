<?php

declare(strict_types=1);

namespace Psl\Tests\Xml\Internal;

use LibXMLError;
use PHPUnit\Framework\TestCase;
use Psl\Xml\Issue\Level;

use function Psl\Xml\Internal\issue_level_from_xml_error;

class IssueLevelFromXmlErrorTest extends TestCase
{
    /**
     * @dataProvider provideErrors
     */
    public function testItCanConstructLevelFromLibXmlError(int $code, ?Level $expected): void
    {
        $error = new LibXMLError();
        $error->level = $code;

        $actual = issue_level_from_xml_error($error);
        if (!$expected) {
            static::assertNull($actual);
            return;
        }

        static::assertTrue($expected->matches($actual));
    }

    public function provideErrors()
    {
        yield 'error' => [
            LIBXML_ERR_ERROR,
            Level::error()
        ];
        yield 'fatal' => [
            LIBXML_ERR_FATAL,
            Level::fatal()
        ];
        yield 'warning' => [
            LIBXML_ERR_WARNING,
            Level::warning()
        ];
        yield 'unknown' => [
            90000,
            null
        ];
    }
}
