<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\Multipart;

use PHPUnit\Framework\TestCase;
use Psl\Encoding\Base64;
use Psl\IO;
use Psl\MIME\ContentDisposition;
use Psl\MIME\Exception\MultiPartException;
use Psl\MIME\MultiPart\Parser;
use Psl\Str;

use function iterator_to_array;

final class ParserTest extends TestCase
{
    public function testSimpleTwoParts(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nHello\r\n--boundary\r\nContent-Type: text/html\r\n\r\n<b>World</b>\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(2, $parts);
        static::assertSame('Hello', $parts[0]->body->readAll());
        static::assertSame('text/plain', $parts[0]->mediaType->essence());
        static::assertSame('<b>World</b>', $parts[1]->body->readAll());
        static::assertSame('text/html', $parts[1]->mediaType->essence());
    }

    public function testFormDataWithContentDisposition(): void
    {
        $body = "--boundary\r\nContent-Disposition: form-data; name=\"field1\"\r\n\r\nvalue1\r\n--boundary\r\nContent-Disposition: form-data; name=\"field2\"; filename=\"file.txt\"\r\nContent-Type: text/plain\r\n\r\nfile content\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(2, $parts);

        $dispositionValue1 = $parts[0]->headers->get('content-disposition');
        static::assertNotNull($dispositionValue1);
        $disposition1 = ContentDisposition::parse($dispositionValue1);
        static::assertSame('field1', $disposition1->parameters->get('name'));

        $dispositionValue2 = $parts[1]->headers->get('content-disposition');
        static::assertNotNull($dispositionValue2);
        $disposition2 = ContentDisposition::parse($dispositionValue2);
        static::assertSame('field2', $disposition2->parameters->get('name'));
        static::assertSame('file.txt', $disposition2->parameters->get('filename'));
    }

    public function testEmptyPart(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('', $parts[0]->body->readAll());
    }

    public function testPreambleIgnored(): void
    {
        $body = "This is the preamble.\r\n--boundary\r\nContent-Type: text/plain\r\n\r\nBody\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('Body', $parts[0]->body->readAll());
    }

    public function testEpilogueIgnored(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nBody\r\n--boundary--\r\nThis is the epilogue.";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('Body', $parts[0]->body->readAll());
    }

    public function testMissingBoundaryThrows(): void
    {
        $this->expectException(MultiPartException::class);

        $parser = new Parser('boundary');
        iterator_to_array($parser->parse(new IO\MemoryHandle('no boundary here')));
    }

    public function testMissingClosingDelimiterThrows(): void
    {
        $this->expectException(MultiPartException::class);

        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nBody\r\n";
        $parser = new Parser('boundary');
        iterator_to_array($parser->parse(new IO\MemoryHandle($body)));
    }

    public function testInvalidBoundaryEmpty(): void
    {
        $this->expectException(MultiPartException::class);

        new Parser('');
    }

    public function testInvalidBoundaryTooLong(): void
    {
        $this->expectException(MultiPartException::class);

        new Parser(Str\repeat('a', 71));
    }

    public function testDefaultContentType(): void
    {
        $body = "--boundary\r\n\r\nno headers\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('text/plain', $parts[0]->mediaType->essence());
    }

    public function testNoContentDisposition(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nBody\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertNull($parts[0]->headers->get('content-disposition'));
    }

    public function testLineFoldedHeaders(): void
    {
        $body = "--boundary\r\nContent-Type:\r\n text/plain\r\n\r\nBody\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('text/plain', $parts[0]->mediaType->essence());
    }

    public function testLfLineEndings(): void
    {
        $body = "--boundary\nContent-Type: text/plain\n\nBody\n--boundary--\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('Body', $parts[0]->body->readAll());
    }

    public function testDecodeBase64TransferEncoding(): void
    {
        $encoded = Base64\encode('Hello, World!');
        $body = "--boundary\r\nContent-Type: text/plain\r\nContent-Transfer-Encoding: base64\r\n\r\n{$encoded}\r\n--boundary--\r\n";

        $parser = new Parser('boundary', decodeTransferEncoding: true);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('Hello, World!', $parts[0]->body->readAll());
    }

    public function testDecodeQuotedPrintableTransferEncoding(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\nHello=20World\r\n--boundary--\r\n";

        $parser = new Parser('boundary', decodeTransferEncoding: true);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('Hello World', $parts[0]->body->readAll());
    }

    public function testDecodeTransferEncodingStripsHeader(): void
    {
        $encoded = Base64\encode('data');
        $body = "--boundary\r\nContent-Type: text/plain\r\nContent-Transfer-Encoding: base64\r\n\r\n{$encoded}\r\n--boundary--\r\n";

        $parser = new Parser('boundary', decodeTransferEncoding: true);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        foreach ($parts[0]->headers->pairs() as [$name, $_]) {
            static::assertNotSame('Content-Transfer-Encoding', $name);
        }
    }

    public function testDecodeTransferEncodingPassthrough7bit(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\nContent-Transfer-Encoding: 7bit\r\n\r\nASCII text\r\n--boundary--\r\n";

        $parser = new Parser('boundary', decodeTransferEncoding: true);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertSame('ASCII text', $parts[0]->body->readAll());
    }

    public function testNoDecodeByDefault(): void
    {
        $encoded = Base64\encode('Hello');
        $body = "--boundary\r\nContent-Type: text/plain\r\nContent-Transfer-Encoding: base64\r\n\r\n{$encoded}\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertSame($encoded, $parts[0]->body->readAll());
    }

    public function testMaxPartsLimitThrows(): void
    {
        $this->expectException(MultiPartException::class);

        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nPart1\r\n--boundary\r\nContent-Type: text/plain\r\n\r\nPart2\r\n--boundary\r\nContent-Type: text/plain\r\n\r\nPart3\r\n--boundary--\r\n";

        $parser = new Parser('boundary', maxParts: 2);
        iterator_to_array($parser->parse(new IO\MemoryHandle($body)));
    }

    public function testMaxPartsZeroIsUnlimited(): void
    {
        $body = '';
        for ($i = 0; $i < 20; $i++) {
            $body .= "--boundary\r\nContent-Type: text/plain\r\n\r\nPart{$i}\r\n";
        }

        $body .= "--boundary--\r\n";

        $parser = new Parser('boundary', maxParts: 0);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(20, $parts);
    }

    public function testCustomSpoolThreshold(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nHello\r\n--boundary--\r\n";

        $parser = new Parser('boundary', spoolThreshold: 1024);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('Hello', $parts[0]->body->readAll());
    }

    public function testLargePartBody(): void
    {
        $largeBody = Str\repeat('x', 100_000);
        $body = "--boundary\r\nContent-Type: application/octet-stream\r\n\r\n{$largeBody}\r\n--boundary--\r\n";

        $parser = new Parser('boundary', spoolThreshold: 1024);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame($largeBody, $parts[0]->body->readAll());
    }

    public function testPartsYieldedLazily(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nFirst\r\n--boundary\r\nContent-Type: text/plain\r\n\r\nSecond\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $generator = $parser->parse(new IO\MemoryHandle($body));

        $first = $generator->current();
        static::assertNotNull($first);
        static::assertSame('First', $first->body->readAll());

        $generator->next();
        $second = $generator->current();
        static::assertNotNull($second);
        static::assertSame('Second', $second->body->readAll());

        $generator->next();
        static::assertFalse($generator->valid());
    }

    public function testMaxPartSizeThrows(): void
    {
        $this->expectException(MultiPartException::class);

        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n" . Str\repeat('A', 1000) . "\r\n--boundary--\r\n";

        $parser = new Parser('boundary', maxPartSize: 500);
        iterator_to_array($parser->parse(new IO\MemoryHandle($body)));
    }

    public function testMaxPartSizeAllowsWithinLimit(): void
    {
        $content = Str\repeat('A', 500);
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n" . $content . "\r\n--boundary--\r\n";

        $parser = new Parser('boundary', maxPartSize: 500);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame($content, $parts[0]->body->readAll());
    }

    public function testMaxPartSizeZeroIsUnlimited(): void
    {
        $content = Str\repeat('A', 10_000);
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n" . $content . "\r\n--boundary--\r\n";

        $parser = new Parser('boundary', maxPartSize: 0);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame($content, $parts[0]->body->readAll());
    }
}
