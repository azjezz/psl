<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\Internal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\MIME\Exception\ParsingException;
use Psl\MIME\Internal\MediaTypeParser;
use Psl\Str;

use function rawurlencode;

final class MediaTypeParserTest extends TestCase
{
    public function testSimpleTextPlain(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('text/plain');

        static::assertSame('text', $type);
        static::assertSame('plain', $subtype);
        static::assertSame([], $params);
    }

    public function testTextHtmlWithCharset(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('text/html; charset=utf-8');

        static::assertSame('text', $type);
        static::assertSame('html', $subtype);
        static::assertSame([['charset', 'utf-8']], $params);
    }

    public function testApplicationJson(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('application/json');

        static::assertSame('application', $type);
        static::assertSame('json', $subtype);
        static::assertSame([], $params);
    }

    public function testCaseNormalization(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('TEXT/HTML');

        static::assertSame('text', $type);
        static::assertSame('html', $subtype);
        static::assertSame([], $params);
    }

    public function testMixedCaseNormalization(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('Application/JSON');

        static::assertSame('application', $type);
        static::assertSame('json', $subtype);
        static::assertSame([], $params);
    }

    public function testQuotedParameterValue(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('text/plain; charset="us-ascii"');

        static::assertSame('text', $type);
        static::assertSame('plain', $subtype);
        static::assertSame([['charset', 'us-ascii']], $params);
    }

    public function testMultipleParameters(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('multipart/form-data; boundary=abc; charset=utf-8');

        static::assertSame('multipart', $type);
        static::assertSame('form-data', $subtype);
        static::assertSame([['boundary', 'abc'], ['charset', 'utf-8']], $params);
    }

    public function testParameterNameCaseNormalization(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('text/plain; CHARSET=utf-8');

        static::assertSame([['charset', 'utf-8']], $params);
    }

    public function testWhitespaceAroundSemicolon(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('text/html ;  charset=utf-8');

        static::assertSame('text', $type);
        static::assertSame('html', $subtype);
        static::assertSame([['charset', 'utf-8']], $params);
    }

    public function testWhitespaceAroundEquals(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('text/html; charset = utf-8');

        static::assertSame('text', $type);
        static::assertSame('html', $subtype);
        static::assertSame([['charset', 'utf-8']], $params);
    }

    public function testLeadingAndTrailingWhitespace(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('  text/plain  ');

        static::assertSame('text', $type);
        static::assertSame('plain', $subtype);
        static::assertSame([], $params);
    }

    public function testStructuredSyntaxSuffix(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('application/vnd.api+json');

        static::assertSame('application', $type);
        static::assertSame('vnd.api+json', $subtype);
        static::assertSame([], $params);
    }

    public function testVendorTree(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('application/vnd.company.app+xml');

        static::assertSame('application', $type);
        static::assertSame('vnd.company.app+xml', $subtype);
        static::assertSame([], $params);
    }

    public function testQuotedStringWithEscaping(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('text/plain; filename="hello \\"world\\""');

        static::assertSame([['filename', 'hello "world"']], $params);
    }

    public function testQuotedStringWithSemicolon(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('text/plain; filename="a;b"');

        static::assertSame([['filename', 'a;b']], $params);
    }

    public function testRfc2231EncodedParameter(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse("text/plain; title*=utf-8'en'This%20is%20fun");

        static::assertSame([['title', 'This is fun']], $params);
    }

    public function testRfc2231Continuations(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse(
            'application/octet-stream; URL*0="ftp://"; URL*1="example.com"',
        );

        static::assertSame([['url', 'ftp://example.com']], $params);
    }

    public function testRfc2231EncodedContinuation(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse(
            "application/octet-stream; title*0*=utf-8'en'This%20is; title*1*=%20fun",
        );

        static::assertSame([['title', 'This is fun']], $params);
    }

    public function testMultipartBoundary(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('multipart/mixed; boundary="----=_Part_123"');

        static::assertSame('multipart', $type);
        static::assertSame('mixed', $subtype);
        static::assertSame([['boundary', '----=_Part_123']], $params);
    }

    public function testTrailingSemicolon(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('text/plain; charset=utf-8;');

        static::assertSame([['charset', 'utf-8']], $params);
    }

    #[DataProvider('invalidMediaTypeProvider')]
    public function testInvalidMediaType(string $input): void
    {
        $this->expectException(ParsingException::class);

        MediaTypeParser::parse($input);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidMediaTypeProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'no slash' => ['texthtml'];
        yield 'no subtype' => ['text/'];
        yield 'no type' => ['/html'];
        yield 'double slash' => ['text//html'];
        yield 'space in type' => ['te xt/html'];
    }

    public function testParseParametersSimple(): void
    {
        $params = MediaTypeParser::parseParameters('; charset=utf-8');

        static::assertSame([['charset', 'utf-8']], $params);
    }

    public function testParseParametersMultiple(): void
    {
        $params = MediaTypeParser::parseParameters('; charset=utf-8; boundary=abc');

        static::assertSame([['charset', 'utf-8'], ['boundary', 'abc']], $params);
    }

    public function testParseParametersEmpty(): void
    {
        $params = MediaTypeParser::parseParameters('');

        static::assertSame([], $params);
    }

    public function testParseParametersWithoutLeadingSemicolon(): void
    {
        $params = MediaTypeParser::parseParameters('charset=utf-8');

        static::assertSame([['charset', 'utf-8']], $params);
    }

    public function testRfc2231EncodedWithPercentEncoding(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse(
            "application/octet-stream; filename*=utf-8''%C3%A9l%C3%A8ve.txt",
        );

        static::assertSame([['filename', "\xC3\xA9l\xC3\xA8ve.txt"]], $params);
    }

    public function testInvalidRfc2231MissingQuotes(): void
    {
        $this->expectException(ParsingException::class);

        MediaTypeParser::parse('text/plain; title*=utf-8noquotes');
    }

    public function testRfc2231NonSequentialContinuationThrows(): void
    {
        $this->expectException(ParsingException::class);

        MediaTypeParser::parse('application/octet-stream; URL*0="ftp://"; URL*2="example.com"');
    }

    public function testRfc2231LeadingZeroInSectionIndex(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse(
            'application/octet-stream; URL*0="ftp://"; URL*01="example.com"',
        );

        static::assertCount(2, $params);
    }

    public function testRfc2231CharsetConversion(): void
    {
        $latin1Value = "\xe9l\xe8ve";
        $encoded = rawurlencode($latin1Value);
        [$type, $subtype, $params] = MediaTypeParser::parse("text/plain; filename*=iso-8859-1''" . $encoded);

        static::assertSame("\xC3\xA9l\xC3\xA8ve", $params[0][1]);
    }

    public function testRfc2231UnsupportedCharsetThrows(): void
    {
        $this->expectException(ParsingException::class);

        MediaTypeParser::parse("text/plain; filename*=x-unknown-charset''hello");
    }

    public function testWildcardType(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('*/*', allowWildcards: true);

        static::assertSame('*', $type);
        static::assertSame('*', $subtype);
        static::assertSame([], $params);
    }

    public function testWildcardSubtype(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('text/*', allowWildcards: true);

        static::assertSame('text', $type);
        static::assertSame('*', $subtype);
        static::assertSame([], $params);
    }

    public function testWildcardTypeRejectedByDefault(): void
    {
        $this->expectException(ParsingException::class);

        MediaTypeParser::parse('*/*');
    }

    public function testWildcardSubtypeRejectedByDefault(): void
    {
        $this->expectException(ParsingException::class);

        MediaTypeParser::parse('text/*');
    }

    public function testWildcardAllWithParams(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse('*/*; q=0.5', allowWildcards: true);

        static::assertSame('*', $type);
        static::assertSame('*', $subtype);
        static::assertSame([['q', '0.5']], $params);
    }

    public function testParseParametersQuotedBackslash(): void
    {
        $params = MediaTypeParser::parseParameters('; path="C:\\\\Users"');

        static::assertSame([['path', 'C:\\Users']], $params);
    }

    public function testParseParametersTrailingWhitespace(): void
    {
        $params = MediaTypeParser::parseParameters('; charset=utf-8   ');

        static::assertSame([['charset', 'utf-8']], $params);
    }

    public function testParseParametersEmptyQuotedValue(): void
    {
        $params = MediaTypeParser::parseParameters('; boundary=""');

        static::assertSame([['boundary', '']], $params);
    }

    public function testTypeTooLongThrows(): void
    {
        $this->expectException(ParsingException::class);

        MediaTypeParser::parse(Str\repeat('a', 128) . '/html');
    }

    public function testSubtypeTooLongThrows(): void
    {
        $this->expectException(ParsingException::class);

        MediaTypeParser::parse('text/' . Str\repeat('a', 128));
    }

    public function testTypeAtMaxLength(): void
    {
        [$type, $subtype, $params] = MediaTypeParser::parse(Str\repeat('a', 127) . '/html');

        static::assertSame(Str\repeat('a', 127), $type);
        static::assertSame('html', $subtype);
    }
}
