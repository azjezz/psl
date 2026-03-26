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

    public function testSpoolThresholdDefault(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nHello\r\n--boundary--\r\n";
        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));
        static::assertCount(1, $parts);
        static::assertSame('Hello', $parts[0]->body->readAll());
    }

    public function testBoundaryExactly70CharsValid(): void
    {
        $boundary = Str\repeat('a', 70);
        $parser = new Parser($boundary);

        $body = "--{$boundary}\r\nContent-Type: text/plain\r\n\r\nTest\r\n--{$boundary}--\r\n";
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('Test', $parts[0]->body->readAll());
    }

    public function testMaxPartsExactlyAtLimit(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nPart1\r\n--boundary\r\nContent-Type: text/plain\r\n\r\nPart2\r\n--boundary--\r\n";

        $parser = new Parser('boundary', maxParts: 2);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(2, $parts);
    }

    public function testMaxPartsExceedingLimitThrows(): void
    {
        $this->expectException(MultiPartException::class);

        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nPart1\r\n--boundary\r\nContent-Type: text/plain\r\n\r\nPart2\r\n--boundary\r\nContent-Type: text/plain\r\n\r\nPart3\r\n--boundary--\r\n";

        $parser = new Parser('boundary', maxParts: 2);
        iterator_to_array($parser->parse(new IO\MemoryHandle($body)));
    }

    public function testEmptyHeaderSectionReturnsNoHeaders(): void
    {
        $body = "--boundary\r\n\r\nbody text\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('text/plain', $parts[0]->mediaType->essence());
    }

    public function testHeadersWithCRLFNormalized(): void
    {
        $body = "--boundary\r\nContent-Type: text/html\r\nX-Custom: value\r\n\r\nbody\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('text/html', $parts[0]->mediaType->essence());
        static::assertSame('value', $parts[0]->headers->get('x-custom'));
    }

    public function testHeaderContinuationWithTab(): void
    {
        $body = "--boundary\r\nContent-Type:\r\n\ttext/plain\r\n\r\nBody\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('text/plain', $parts[0]->mediaType->essence());
    }

    public function testHeaderContinuationPreservesAndAppendsValue(): void
    {
        $body = "--boundary\r\nX-Long-Header: first part\r\n second part\r\n\r\nBody\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        $value = $parts[0]->headers->get('x-long-header');
        static::assertNotNull($value);
        static::assertSame('first part second part', $value);
    }

    public function testMultipleHeaderContinuationLines(): void
    {
        $body = "--boundary\r\nX-Multi: part1\r\n part2\r\n part3\r\n\r\nBody\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        $value = $parts[0]->headers->get('x-multi');
        static::assertNotNull($value);
        static::assertStringContainsString('part1', $value);
        static::assertStringContainsString('part2', $value);
        static::assertStringContainsString('part3', $value);
    }

    public function testHeaderNameAndValueParsedCorrectly(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\nX-Test:  value-with-spaces  \r\n\r\nBody\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('value-with-spaces', $parts[0]->headers->get('x-test'));
    }

    public function testHeaderValueStartsAfterColon(): void
    {
        $body = "--boundary\r\nX-NoSpace:value\r\n\r\nBody\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('value', $parts[0]->headers->get('x-nospace'));
    }

    public function testCloseDelimiterStopsParsing(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nFirst\r\n--boundary--\r\nSome trailing epilogue";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('First', $parts[0]->body->readAll());
    }

    public function testDecodeTransferEncoding8bitPassthrough(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\nContent-Transfer-Encoding: 8bit\r\n\r\nHigh bytes: \xC3\xA9\r\n--boundary--\r\n";

        $parser = new Parser('boundary', decodeTransferEncoding: true);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame("High bytes: \xC3\xA9", $parts[0]->body->readAll());
    }

    public function testDecodeTransferEncodingBinaryPassthrough(): void
    {
        $body = "--boundary\r\nContent-Type: application/octet-stream\r\nContent-Transfer-Encoding: binary\r\n\r\n\x00\x01\x02\r\n--boundary--\r\n";

        $parser = new Parser('boundary', decodeTransferEncoding: true);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame("\x00\x01\x02", $parts[0]->body->readAll());
    }

    public function testMaxPartSizeExactlyAtLimitSucceeds(): void
    {
        $content = Str\repeat('A', 100);
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n" . $content . "\r\n--boundary--\r\n";

        $parser = new Parser('boundary', maxPartSize: 100);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame($content, $parts[0]->body->readAll());
    }

    public function testMaxPartSizeExceededByOneThrows(): void
    {
        $this->expectException(MultiPartException::class);

        $content = Str\repeat('A', 101);
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n" . $content . "\r\n--boundary--\r\n";

        $parser = new Parser('boundary', maxPartSize: 100);
        iterator_to_array($parser->parse(new IO\MemoryHandle($body)));
    }

    public function testNoBoundaryThrows(): void
    {
        $this->expectException(MultiPartException::class);

        $parser = new Parser('boundary');
        iterator_to_array($parser->parse(new IO\MemoryHandle('this has no boundary at all')));
    }

    public function testDecodeTransferEncodingCaseInsensitive(): void
    {
        $encoded = Base64\encode('Test');
        $body = "--boundary\r\nContent-Type: text/plain\r\nCONTENT-TRANSFER-ENCODING: base64\r\n\r\n{$encoded}\r\n--boundary--\r\n";

        $parser = new Parser('boundary', decodeTransferEncoding: true);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('Test', $parts[0]->body->readAll());
    }

    public function testDecodeTransferEncodingValueTrimmed(): void
    {
        $encoded = Base64\encode('Trimmed');
        $body = "--boundary\r\nContent-Type: text/plain\r\nContent-Transfer-Encoding:  base64 \r\n\r\n{$encoded}\r\n--boundary--\r\n";

        $parser = new Parser('boundary', decodeTransferEncoding: true);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('Trimmed', $parts[0]->body->readAll());
    }

    public function testDefaultSpoolThresholdIs2MB(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nHello\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
    }

    public function testDefaultMaxPartsIsZeroUnlimited(): void
    {
        $body = '';
        for ($i = 0; $i < 5; $i++) {
            $body .= "--boundary\r\nContent-Type: text/plain\r\n\r\nPart{$i}\r\n";
        }

        $body .= "--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(5, $parts);
    }

    public function testDefaultMaxPartSizeIsZeroUnlimited(): void
    {
        $content = Str\repeat('X', 5000);
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n{$content}\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame($content, $parts[0]->body->readAll());
    }

    public function testMissingBoundaryThrowsWithThrowKeyword(): void
    {
        $this->expectException(MultiPartException::class);

        $parser = new Parser('boundary');
        iterator_to_array($parser->parse(new IO\MemoryHandle('no boundary here at all')));
    }

    public function testCloseDelimiterBreaksLoop(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nFirst\r\n--boundary--\r\n--boundary\r\nContent-Type: text/plain\r\n\r\nSecond\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('First', $parts[0]->body->readAll());
    }

    public function testEmptyHeaderSectionReturnsEmptyArray(): void
    {
        $body = "--boundary\r\n\r\nno-header body\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('text/plain', $parts[0]->mediaType->essence());
    }

    public function testHeaderSectionCRLFToLFNormalization(): void
    {
        $body = "--boundary\r\nContent-Type: text/html\r\nX-Custom: value\r\n\r\nbody\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('text/html', $parts[0]->mediaType->essence());
        static::assertSame('value', $parts[0]->headers->get('x-custom'));
    }

    public function testHeaderNameTrimmed(): void
    {
        $body = "--boundary\r\n Content-Type : text/plain\r\n\r\nBody\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
    }

    public function testParserBodyContentPreservedExactly(): void
    {
        $content = 'Hello, World! This is some content.';
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n{$content}\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame($content, $parts[0]->body->readAll());
    }

    public function testMultiplePartsBodyContent(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nAlpha\r\n--boundary\r\nContent-Type: text/plain\r\n\r\nBravo\r\n--boundary\r\nContent-Type: text/plain\r\n\r\nCharlie\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(3, $parts);
        static::assertSame('Alpha', $parts[0]->body->readAll());
        static::assertSame('Bravo', $parts[1]->body->readAll());
        static::assertSame('Charlie', $parts[2]->body->readAll());
    }

    public function testMaxPartSizeExceededThrowsWithCorrectMessage(): void
    {
        $content = Str\repeat('A', 200);
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n{$content}\r\n--boundary--\r\n";

        try {
            $parser = new Parser('boundary', maxPartSize: 100);
            iterator_to_array($parser->parse(new IO\MemoryHandle($body)));
            static::fail('Expected exception');
        } catch (MultiPartException $e) {
            static::assertStringContainsString('part body exceeds maximum size of 100 bytes', $e->getMessage());
        }
    }

    public function testLargeBodyStreamedCorrectly(): void
    {
        $content = Str\repeat('Z', 50_000);
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n{$content}\r\n--boundary--\r\n";

        $parser = new Parser('boundary', spoolThreshold: 1024);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame(50_000, Str\Byte\length($parts[0]->body->readAll()));
    }

    public function testMaxPartSizeExactBoundaryNoThrow(): void
    {
        $content = Str\repeat('B', 50);
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n{$content}\r\n--boundary--\r\n";

        $parser = new Parser('boundary', maxPartSize: 50);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame($content, $parts[0]->body->readAll());
    }

    public function testMaxPartSizeExceededByOneByte(): void
    {
        $this->expectException(MultiPartException::class);

        $content = Str\repeat('C', 51);
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n{$content}\r\n--boundary--\r\n";

        $parser = new Parser('boundary', maxPartSize: 50);
        iterator_to_array($parser->parse(new IO\MemoryHandle($body)));
    }

    public function testTwoPartsSecondBodyReadCorrectly(): void
    {
        $body = "--delim\r\nContent-Type: text/plain\r\n\r\nAAA\r\n--delim\r\nContent-Type: text/plain\r\n\r\nBBB\r\n--delim--\r\n";

        $parser = new Parser('delim');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(2, $parts);
        static::assertSame('AAA', $parts[0]->body->readAll());
        static::assertSame('BBB', $parts[1]->body->readAll());
    }

    public function testDecodeTransferEncodingMixedCase(): void
    {
        $encoded = Base64\encode('CaseTest');
        $body = "--boundary\r\nContent-Type: text/plain\r\nContent-Transfer-Encoding: BASE64\r\n\r\n{$encoded}\r\n--boundary--\r\n";

        $parser = new Parser('boundary', decodeTransferEncoding: true);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('CaseTest', $parts[0]->body->readAll());
    }

    public function testMaxPartSizeEnforcedInStreamBodyToSpool(): void
    {
        $content = Str\repeat('A', 10_000);
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n{$content}\r\n--boundary--\r\n";

        try {
            $parser = new Parser('boundary', maxPartSize: 5_000);
            iterator_to_array($parser->parse(new IO\MemoryHandle($body)));
            static::fail('Expected exception');
        } catch (MultiPartException $e) {
            static::assertStringContainsString('part body exceeds maximum size of', $e->getMessage());
            static::assertStringContainsString('5000', $e->getMessage());
            static::assertStringContainsString('bytes', $e->getMessage());
        }
    }

    public function testMaxPartSizeErrorMessageFormat(): void
    {
        try {
            $content = Str\repeat('Z', 300);
            $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n{$content}\r\n--boundary--\r\n";

            $parser = new Parser('boundary', maxPartSize: 100);
            iterator_to_array($parser->parse(new IO\MemoryHandle($body)));
            static::fail('Expected exception');
        } catch (MultiPartException $e) {
            $message = $e->getMessage();
            static::assertSame('Malformed multipart body: part body exceeds maximum size of 100 bytes.', $message);
        }
    }

    public function testBodyWithContentBeforeBoundary(): void
    {
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\nSomePrefix\r\n--boundary\r\nContent-Type: text/plain\r\n\r\nBody2\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(2, $parts);
        static::assertSame('SomePrefix', $parts[0]->body->readAll());
        static::assertSame('Body2', $parts[1]->body->readAll());
    }

    public function testLargeBodyContentIntegrity(): void
    {
        $uniqueStart = 'START_MARKER_';
        $uniqueEnd = '_END_MARKER';
        $padding = Str\repeat('X', 20_000);
        $content = $uniqueStart . $padding . $uniqueEnd;
        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n{$content}\r\n--boundary--\r\n";

        $parser = new Parser('boundary', spoolThreshold: 256);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        $result = $parts[0]->body->readAll();
        static::assertSame($content, $result);
        static::assertStringStartsWith($uniqueStart, $result);
        static::assertStringEndsWith($uniqueEnd, $result);
    }

    public function testMultipleLargePartsIntegrity(): void
    {
        $content1 = Str\repeat('A', 15_000);
        $content2 = Str\repeat('B', 15_000);

        $body = "--boundary\r\nContent-Type: text/plain\r\n\r\n{$content1}\r\n--boundary\r\nContent-Type: text/plain\r\n\r\n{$content2}\r\n--boundary--\r\n";

        $parser = new Parser('boundary', spoolThreshold: 256);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(2, $parts);
        static::assertSame($content1, $parts[0]->body->readAll());
        static::assertSame($content2, $parts[1]->body->readAll());
    }

    public function testHeaderNameWithLeadingSpaceTrimmed(): void
    {
        $body = "--boundary\r\n  Content-Type  : text/plain\r\n\r\nBody\r\n--boundary--\r\n";

        $parser = new Parser('boundary');
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
    }

    public function testDecodeTransferEncodingPreservesOtherHeaders(): void
    {
        $encoded = Base64\encode('Preserved');
        $body = "--boundary\r\nContent-Type: text/plain\r\nContent-Transfer-Encoding: base64\r\nX-Custom: keep-me\r\n\r\n{$encoded}\r\n--boundary--\r\n";

        $parser = new Parser('boundary', decodeTransferEncoding: true);
        $parts = iterator_to_array($parser->parse(new IO\MemoryHandle($body)));

        static::assertCount(1, $parts);
        static::assertSame('Preserved', $parts[0]->body->readAll());
        static::assertSame('keep-me', $parts[0]->headers->get('x-custom'));
        static::assertNull($parts[0]->headers->get('content-transfer-encoding'));
    }
}
