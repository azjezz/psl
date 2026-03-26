<?php

declare(strict_types=1);

namespace Psl\IRI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\IRI;
use Psl\IRI\Exception\InvalidIRIException;
use Psl\URI\Authority\IPHost;
use Psl\URI\Authority\RegisteredNameHost;

use function array_fill;
use function function_exists;
use function implode;
use function mb_strlen;

final class ParseTest extends TestCase
{
    public function testUnicodeHost(): void
    {
        $iri = IRI\parse('http://例え.jp/');

        static::assertNotNull($iri->authority);
        static::assertInstanceOf(RegisteredNameHost::class, $iri->authority->host);
        static::assertSame('例え.jp', $iri->authority->host->name);
    }

    public function testUnicodePath(): void
    {
        $iri = IRI\parse('http://example.com/パス');

        static::assertSame('/パス', $iri->path);
    }

    public function testUnicodeQuery(): void
    {
        $iri = IRI\parse('http://example.com/?q=日本語');

        static::assertSame('q=日本語', $iri->query);
    }

    public function testUnicodeFragment(): void
    {
        $iri = IRI\parse('http://example.com/#フラグ');

        static::assertSame('フラグ', $iri->fragment);
    }

    public function testFullUnicodeIRI(): void
    {
        $iri = IRI\parse('http://例え.jp/パス?q=日本語#フラグ');

        static::assertSame('http', $iri->scheme);
        static::assertNotNull($iri->authority);
        static::assertInstanceOf(RegisteredNameHost::class, $iri->authority->host);
        static::assertSame('例え.jp', $iri->authority->host->name);
        static::assertSame('/パス', $iri->path);
        static::assertSame('q=日本語', $iri->query);
        static::assertSame('フラグ', $iri->fragment);
    }

    public function testASCIIPassthrough(): void
    {
        $iri = IRI\parse('http://example.com/path?q=1#f');

        static::assertSame('http', $iri->scheme);
        static::assertNotNull($iri->authority);
        static::assertInstanceOf(RegisteredNameHost::class, $iri->authority->host);
        static::assertSame('example.com', $iri->authority->host->name);
        static::assertSame('/path', $iri->path);
        static::assertSame('q=1', $iri->query);
        static::assertSame('f', $iri->fragment);
    }

    public function testPrivateUseInQuery(): void
    {
        $iri = IRI\parse("http://example.com/?q=\xEE\x80\x80");

        static::assertSame("q=\xEE\x80\x80", $iri->query);
    }

    public function testPrivateUseOutsideQueryRejected(): void
    {
        $this->expectException(InvalidIRIException::class);

        IRI\parse("http://example.com/\xEE\x80\x80");
    }

    public function testSchemeNormalization(): void
    {
        $iri = IRI\parse('HTTP://例え.jp/');

        static::assertSame('http', $iri->scheme);
    }

    public function testIPv6Host(): void
    {
        $iri = IRI\parse('http://[::1]/');

        static::assertInstanceOf(IPHost::class, $iri->authority?->host);
    }

    public function testNFCNormalization(): void
    {
        if (!function_exists('normalizer_normalize')) {
            static::markTestSkipped('intl extension required');
        }

        $decomposed = "http://example.com/\xC3\xBC";
        $composed = "http://example.com/\xC3\xBC";

        $iriDecomposed = IRI\parse($decomposed);
        $iriComposed = IRI\parse($composed);

        static::assertSame($iriComposed->path, $iriDecomposed->path);
    }

    public function testArabicHost(): void
    {
        $iri = IRI\parse('http://مثال.إختبار/');

        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertInstanceOf(RegisteredNameHost::class, $authority->host);
        static::assertSame('مثال.إختبار', $authority->host->name);
    }

    public function testChineseHost(): void
    {
        $iri = IRI\parse('http://中文.公司.cn/');

        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertInstanceOf(RegisteredNameHost::class, $authority->host);
        static::assertSame('中文.公司.cn', $authority->host->name);
    }

    public function testKoreanPath(): void
    {
        $iri = IRI\parse('http://example.com/한국어/경로');

        static::assertSame('/한국어/경로', $iri->path);
    }

    public function testCyrillicHost(): void
    {
        $iri = IRI\parse('http://пример.испытание/');

        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertInstanceOf(RegisteredNameHost::class, $authority->host);
        static::assertSame('пример.испытание', $authority->host->name);
    }

    public function testMixedScriptsInPathAndQuery(): void
    {
        $iri = IRI\parse('http://example.com/日本語/Ελληνικά?q=العربية#кириллица');

        static::assertSame('/日本語/Ελληνικά', $iri->path);
        static::assertSame('q=العربية', $iri->query);
        static::assertSame('кириллица', $iri->fragment);
    }

    public function testUnicodeInUserInfo(): void
    {
        $iri = IRI\parse('http://пользователь@example.com/');

        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertSame('пользователь', $authority->userInfo);
    }

    public function testEncodedAndUnencodedMixInPath(): void
    {
        $iri = IRI\parse('http://example.com/%E3%83%91%E3%82%B9/日本語');

        static::assertSame('/%E3%83%91%E3%82%B9/日本語', $iri->path);
    }

    public function testSupplementaryPlaneCharacterInPath(): void
    {
        $iri = IRI\parse("http://example.com/\xF0\x9D\x84\x9E");

        static::assertSame("/\xF0\x9D\x84\x9E", $iri->path);
    }

    public function testSupplementaryPlaneCharacterU10000(): void
    {
        $iri = IRI\parse("http://example.com/\u{10000}");

        static::assertSame("/\u{10000}", $iri->path);
    }

    public function testUcscharBoundaryLowerU00A0(): void
    {
        $iri = IRI\parse("http://example.com/\u{00A0}test");

        static::assertSame("/\u{00A0}test", $iri->path);
    }

    public function testUcscharBoundaryUpperU_D7FF(): void
    {
        $iri = IRI\parse("http://example.com/\u{D7FF}");

        static::assertSame("/\u{D7FF}", $iri->path);
    }

    public function testUcscharBoundaryU_F900(): void
    {
        $iri = IRI\parse("http://example.com/\u{F900}");

        static::assertStringStartsWith('/', $iri->path);
        static::assertSame(2, mb_strlen($iri->path));
    }

    public function testUcscharBoundaryU_FDCF(): void
    {
        $iri = IRI\parse("http://example.com/\u{FDCF}");

        static::assertSame("/\u{FDCF}", $iri->path);
    }

    public function testUcscharBoundaryU_FDF0(): void
    {
        $iri = IRI\parse("http://example.com/\u{FDF0}");

        static::assertSame("/\u{FDF0}", $iri->path);
    }

    public function testUcscharBoundaryU_FFEF(): void
    {
        $iri = IRI\parse("http://example.com/\u{FFEF}");

        static::assertSame("/\u{FFEF}", $iri->path);
    }

    public function testUcscharPlane1U_1FFFD(): void
    {
        $iri = IRI\parse("http://example.com/\u{1FFFD}");

        static::assertSame("/\u{1FFFD}", $iri->path);
    }

    public function testASCIIOnlyPassthrough(): void
    {
        $iri = IRI\parse('http://user:pass@example.com:8080/a/b/c?x=1&y=2#sec');

        static::assertSame('http', $iri->scheme);
        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertSame('user:pass', $authority->userInfo);
        static::assertInstanceOf(RegisteredNameHost::class, $authority->host);
        static::assertSame('example.com', $authority->host->name);
        static::assertSame(8080, $authority->port);
        static::assertSame('/a/b/c', $iri->path);
        static::assertSame('x=1&y=2', $iri->query);
        static::assertSame('sec', $iri->fragment);
    }

    public function testVeryLongUnicodePath(): void
    {
        $segment = '日本語';
        $longPath = '/' . implode('/', array_fill(0, 100, $segment));
        $iri = IRI\parse('http://example.com' . $longPath);

        static::assertSame($longPath, $iri->path);
    }

    public function testNFDToNFCNormalization(): void
    {
        $nfd = "http://example.com/u\xCC\x88ber";
        $nfc = "http://example.com/\xC3\xBCber";

        $iriNfd = IRI\parse($nfd);
        $iriNfc = IRI\parse($nfc);

        static::assertSame($iriNfc->path, $iriNfd->path);
    }

    public function testEmptyPathWithUnicodeQuery(): void
    {
        $iri = IRI\parse('http://example.com?q=日本語');

        static::assertSame('', $iri->path);
        static::assertSame('q=日本語', $iri->query);
    }

    public function testCharacterU0080Rejected(): void
    {
        $this->expectException(InvalidIRIException::class);

        IRI\parse("http://example.com/\xC2\x80");
    }

    public function testNonCharacterFFFERejectedInPath(): void
    {
        $this->expectException(InvalidIRIException::class);

        IRI\parse("http://example.com/\u{FFFE}");
    }

    public function testNonCharacterFFFFRejectedInPath(): void
    {
        $this->expectException(InvalidIRIException::class);

        IRI\parse("http://example.com/\u{FFFF}");
    }

    public function testPrivateUseInQueryAllowed(): void
    {
        $iri = IRI\parse("http://example.com/?q=\u{E000}");

        static::assertSame("q=\u{E000}", $iri->query);
    }

    public function testPrivateUseInFragmentRejected(): void
    {
        $this->expectException(InvalidIRIException::class);

        IRI\parse("http://example.com/#\u{E000}");
    }

    public function testIPv6WithUnicodePath(): void
    {
        $iri = IRI\parse('http://[::1]/パス');

        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertInstanceOf(IPHost::class, $authority->host);
        static::assertSame('/パス', $iri->path);
    }

    public function testSchemeWithUnicodeHostAndPort(): void
    {
        $iri = IRI\parse('https://例え.jp:443/');

        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertSame(443, $authority->port);
        static::assertInstanceOf(RegisteredNameHost::class, $authority->host);
        static::assertSame('例え.jp', $authority->host->name);
    }

    public function testIPv4HostReturnedAsIs(): void
    {
        $iri = IRI\parse('http://192.168.1.1/path');

        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertInstanceOf(IPHost::class, $authority->host);
        static::assertSame('192.168.1.1', $authority->host->address->toString());
    }

    public function testIPv6HostReturnedAsIs(): void
    {
        $iri = IRI\parse('http://[::1]/path');

        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertInstanceOf(IPHost::class, $authority->host);
        static::assertSame('::1', $authority->host->address->toString());
    }

    public function testIPv6WithZoneId(): void
    {
        $iri = IRI\parse('http://[fe80::1%25eth0]/path');

        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertInstanceOf(IPHost::class, $authority->host);
        static::assertSame('fe80::1', $authority->host->address->toString());
        static::assertSame('eth0', $authority->host->zone);
    }

    public function testInvalidBracketedHostFallsToRegisteredName(): void
    {
        $iri = IRI\parse('http://[not-an-ip]/path');

        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertInstanceOf(RegisteredNameHost::class, $authority->host);
        static::assertSame('[not-an-ip]', $authority->host->name);
    }

    public function testInvalidSchemeThrows(): void
    {
        $this->expectException(InvalidIRIException::class);

        IRI\parse('1bad-scheme://example.com');
    }

    public function testRegexFailureReturnsEmptyIRI(): void
    {
        $iri = IRI\parse('');

        static::assertNull($iri->scheme);
        static::assertNull($iri->authority);
        static::assertSame('', $iri->path);
    }

    public function testIPv4LikeButInvalidFallsToRegisteredName(): void
    {
        $iri = IRI\parse('http://999.999.999.999/path');

        $authority = $iri->authority;
        static::assertNotNull($authority);
        static::assertInstanceOf(RegisteredNameHost::class, $authority->host);
        static::assertSame('999.999.999.999', $authority->host->name);
    }
}
