<?php

declare(strict_types=1);

namespace Psl\Tests\Xml\Internal;

use LibXMLError;
use PHPUnit\Framework\TestCase;
use Psl\Xml\Issue\Level;

use function Psl\Xml\Internal\issue_from_xml_error;

class IssueFromXmlErrorTest extends TestCase
{
    public function testItCanConstructIssueFromLibXmlError(): void
    {
        $error = new LibXMLError();
        $error->level   = Level::error()->value();
        $error->file     = 'file.xml';
        $error->message = 'message';
        $error->line    = 99;
        $error->code    = 1;
        $error->column  = 10;

        $issue = issue_from_xml_error($error);

        static::assertSame($error->level, $issue->level()->value());
        static::assertSame($error->file, $issue->file());
        static::assertSame($error->message, $issue->message());
        static::assertSame($error->line, $issue->line());
        static::assertSame($error->code, $issue->code());
        static::assertSame($error->column, $issue->column());
    }

    public function testItReturnsNullOnInvalidLevel(): void
    {
        $error = new LibXMLError();
        $error->level = 9000;
        $issue = issue_from_xml_error($error);

        static::assertNull($issue);
    }
}
