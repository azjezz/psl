<?php

declare(strict_types=1);

namespace Psl\IRI\Tests\Unit\Internal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\IRI\Exception\PunycodeException;
use Psl\IRI\Internal\Punycode;

use function str_repeat;

final class PunycodeTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function encodeProvider(): iterable
    {
        yield 'German münchen' => ['münchen', 'mnchen-3ya'];
        yield 'Japanese 例え' => ['例え', 'r8jz45g'];
        yield 'German bücher' => ['bücher', 'bcher-kva'];
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function decodeProvider(): iterable
    {
        yield 'German münchen' => ['mnchen-3ya', 'münchen'];
        yield 'Japanese 例え' => ['r8jz45g', '例え'];
        yield 'German bücher' => ['bcher-kva', 'bücher'];
    }

    #[DataProvider('encodeProvider')]
    public function testEncode(string $input, string $expected): void
    {
        static::assertSame($expected, Punycode::encode($input));
    }

    #[DataProvider('decodeProvider')]
    public function testDecode(string $input, string $expected): void
    {
        static::assertSame($expected, Punycode::decode($input));
    }

    public function testDecodeRoundTrip(): void
    {
        $inputs = ['münchen', '例え', 'bücher'];

        foreach ($inputs as $input) {
            $encoded = Punycode::encode($input);
            $decoded = Punycode::decode($encoded);

            static::assertSame($input, $decoded);
        }
    }

    public function testDecodeBadInput(): void
    {
        $this->expectException(PunycodeException::class);

        Punycode::decode('!!!invalid!!!');
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function rfc3492VectorsEncodeProvider(): iterable
    {
        yield 'Arabic (Egyptian)' => ['ليهمابتكلموشعربي؟', 'egbpdaj6bu4bxfgehfvwxn'];
        yield 'Chinese (simplified)' => ['他们为什么不说中文', 'ihqwcrb4cv8a8dqg056pqjye'];
        yield 'Chinese (traditional)' => ['他們爲什麽不說中文', 'ihqwctvzc91f659drss3x8bo0yb'];
        yield 'Hindi (Devanagari)' => ['यहलोगहिन्दीक्योंनहींबोलसकतेहैं', 'i1baa7eci9glrd9b2ae1bj0hfcgg6iyaf8o0a1dig0cd'];
        yield 'Japanese (kanji+hiragana)' => [
            'なぜみんな日本語を話してくれないのか',
            'n8jok5ay5dzabd5bym9f0cm5685rrjetr6pdxa',
        ];
        yield 'Russian (Cyrillic)' => ['почемужеонинеговорятпорусски', 'b1abfaaepdrnnbgefbadotcwatmq2g4l'];
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function rfc3492VectorsDecodeProvider(): iterable
    {
        yield 'Arabic (Egyptian)' => ['egbpdaj6bu4bxfgehfvwxn', 'ليهمابتكلموشعربي؟'];
        yield 'Chinese (simplified)' => ['ihqwcrb4cv8a8dqg056pqjye', '他们为什么不说中文'];
        yield 'Chinese (traditional)' => ['ihqwctvzc91f659drss3x8bo0yb', '他們爲什麽不說中文'];
        yield 'Russian (Cyrillic)' => ['b1abfaaepdrnnbgefbadotcwatmq2g4l', 'почемужеонинеговорятпорусски'];
    }

    #[DataProvider('rfc3492VectorsEncodeProvider')]
    public function testRFC3492VectorEncode(string $input, string $expected): void
    {
        static::assertSame($expected, Punycode::encode($input));
    }

    #[DataProvider('rfc3492VectorsDecodeProvider')]
    public function testRFC3492VectorDecode(string $input, string $expected): void
    {
        static::assertSame($expected, Punycode::decode($input));
    }

    public function testPureASCIIInputUnchanged(): void
    {
        static::assertSame('example', Punycode::encode('example'));
    }

    public function testPureASCIIEncodeDecodeWithDelimiter(): void
    {
        $encoded = Punycode::encode('abc');
        static::assertSame('abc', $encoded);

        $input = 'abcü';
        $encoded = Punycode::encode($input);
        $decoded = Punycode::decode($encoded);
        static::assertSame($input, $decoded);
    }

    public function testSingleASCIICharacter(): void
    {
        static::assertSame('a', Punycode::encode('a'));
    }

    public function testSingleUnicodeCharacter(): void
    {
        $encoded = Punycode::encode('ü');
        $decoded = Punycode::decode($encoded);

        static::assertSame('ü', $decoded);
    }

    public function testVeryLongUnicodeString(): void
    {
        $input = str_repeat('日本', 50);
        $encoded = Punycode::encode($input);
        $decoded = Punycode::decode($encoded);

        static::assertSame($input, $decoded);
    }

    public function testEmptyStringEncode(): void
    {
        static::assertSame('', Punycode::encode(''));
    }

    public function testEmptyStringDecode(): void
    {
        static::assertSame('', Punycode::decode(''));
    }

    public function testMixedASCIIAndUnicode(): void
    {
        $input = 'hello世界';
        $encoded = Punycode::encode($input);
        $decoded = Punycode::decode($encoded);

        static::assertSame($input, $decoded);
    }
}
