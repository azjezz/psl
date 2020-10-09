<?php

declare(strict_types=1);

namespace Psl\Tests\Xml\Internal;

use LibXMLError;
use PHPUnit\Framework\TestCase;
use Psl\Xml\Issue\Level;

use function Psl\Xml\Internal\issue_collection_from_xml_errors;

class IssueCollectionFromXmlErrorsTest extends TestCase
{
    public function testItCanConstructIssueCollectionFromLibXmlErrors(): void
    {
        $errors = [
            $error1 = $this->createError(LIBXML_ERR_WARNING),
            $error2 = $this->createError(LIBXML_ERR_FATAL),
            $error3 = $this->createError(90000),
        ];

        $issues = issue_collection_from_xml_errors($errors);
        $extracted = [...$issues];

        static::assertCount(2, $issues);
        static::assertTrue($extracted[0]->level()->matches(Level::warning()));
        static::assertTrue($extracted[1]->level()->matches(Level::fatal()));
    }

    private function createError(int $level): LibXMLError
    {
        $error = new LibXMLError();
        $error->level   = $level;
        $error->file     = 'file.xml';
        $error->message = 'message';
        $error->line    = 99;
        $error->code    = 1;
        $error->column  = 10;

        return $error;
    }
}
