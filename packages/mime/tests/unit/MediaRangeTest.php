<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\MIME\Exception\InvalidMediaTypeComponentException;
use Psl\MIME\MediaRange;
use Psl\MIME\MediaType;
use Psl\MIME\Parameters;
use Psl\Str;
use Stringable;

final class MediaRangeTest extends TestCase
{
    public function testConstructExact(): void
    {
        $range = new MediaRange('text', 'html');

        static::assertSame('text', $range->type);
        static::assertSame('html', $range->subtype);
    }

    public function testConstructWildcardAll(): void
    {
        $range = new MediaRange('*', '*');

        static::assertSame('*', $range->type);
        static::assertSame('*', $range->subtype);
    }

    public function testConstructWildcardSubtype(): void
    {
        $range = new MediaRange('text', '*');

        static::assertSame('text', $range->type);
        static::assertSame('*', $range->subtype);
    }

    public function testConstructWildcardTypeConcreteSubtypeRejected(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('*', 'html');
    }

    public function testConstructNormalizesCase(): void
    {
        $range = new MediaRange('TEXT', 'HTML');

        static::assertSame('text', $range->type);
        static::assertSame('html', $range->subtype);
    }

    public function testConstructWithParameters(): void
    {
        $params = Parameters::fromPairs([['charset', 'utf-8']]);
        $range = new MediaRange('text', 'html', $params);

        static::assertSame('utf-8', $range->parameters->get('charset'));
    }

    public function testConstructEmptyTypeRejected(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('', 'html');
    }

    public function testConstructEmptySubtypeRejected(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('text', '');
    }

    public function testParseWildcardAll(): void
    {
        $range = MediaRange::parse('*/*');

        static::assertSame('*', $range->type);
        static::assertSame('*', $range->subtype);
    }

    public function testParseWildcardSubtype(): void
    {
        $range = MediaRange::parse('text/*');

        static::assertSame('text', $range->type);
        static::assertSame('*', $range->subtype);
    }

    public function testParseExact(): void
    {
        $range = MediaRange::parse('application/json');

        static::assertSame('application', $range->type);
        static::assertSame('json', $range->subtype);
    }

    public function testParseWithParameters(): void
    {
        $range = MediaRange::parse('text/html; charset=utf-8');

        static::assertSame('text', $range->type);
        static::assertSame('html', $range->subtype);
        static::assertSame('utf-8', $range->parameters->get('charset'));
    }

    public function testFromMediaType(): void
    {
        $type = new MediaType('text', 'html');
        $range = MediaRange::fromMediaType($type);

        static::assertSame('text', $range->type);
        static::assertSame('html', $range->subtype);
    }

    public function testMatchesExact(): void
    {
        $range = new MediaRange('text', 'html');
        $type = new MediaType('text', 'html');

        static::assertTrue($range->matches($type));
    }

    public function testMatchesDifferentType(): void
    {
        $range = new MediaRange('text', 'html');
        $type = new MediaType('application', 'html');

        static::assertFalse($range->matches($type));
    }

    public function testMatchesDifferentSubtype(): void
    {
        $range = new MediaRange('text', 'html');
        $type = new MediaType('text', 'plain');

        static::assertFalse($range->matches($type));
    }

    public function testMatchesWildcardAll(): void
    {
        $range = new MediaRange('*', '*');
        $type = new MediaType('application', 'json');

        static::assertTrue($range->matches($type));
    }

    public function testMatchesWildcardSubtype(): void
    {
        $range = new MediaRange('text', '*');
        $type = new MediaType('text', 'html');

        static::assertTrue($range->matches($type));
    }

    public function testMatchesWildcardSubtypeDifferentType(): void
    {
        $range = new MediaRange('text', '*');
        $type = new MediaType('application', 'json');

        static::assertFalse($range->matches($type));
    }

    public function testMatchesIgnoresParameters(): void
    {
        $range = MediaRange::parse('text/html; charset=utf-8');
        $type = MediaType::parse('text/html; charset=iso-8859-1');

        static::assertTrue($range->matches($type));
    }

    public function testEssenceExact(): void
    {
        $range = new MediaRange('text', 'html');

        static::assertSame('text/html', $range->essence());
    }

    public function testEssenceWildcard(): void
    {
        $range = new MediaRange('*', '*');

        static::assertSame('*/*', $range->essence());
    }

    public function testToString(): void
    {
        $range = MediaRange::parse('text/html; charset=utf-8');

        static::assertSame('text/html; charset=utf-8', $range->toString());
    }

    public function testToStringWildcard(): void
    {
        $range = new MediaRange('*', '*');

        static::assertSame('*/*', $range->toString());
    }

    public function testStringable(): void
    {
        $range = new MediaRange('text', 'html');

        static::assertSame('text/html', (string) $range);
    }

    public function testStringableInterface(): void
    {
        $range = new MediaRange('text', 'html');

        static::assertInstanceOf(Stringable::class, $range);
    }

    public function testTypeTooLongThrows(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange(Str\repeat('a', 128), 'html');
    }

    public function testSubtypeTooLongThrows(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('text', Str\repeat('a', 128));
    }

    public function testInvalidCharInTypeThrows(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('te/xt', 'html');
    }

    public function testInvalidCharInSubtypeThrows(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('text', 'pl@in');
    }

    public function testParseWildcardSubtypeWithParams(): void
    {
        $range = MediaRange::parse('text/*; q=0.8');

        static::assertSame('text', $range->type);
        static::assertSame('*', $range->subtype);
        static::assertSame(0.8, $range->weight);
        static::assertNull($range->parameters->get('q'));
    }

    public function testParseWildcardAllWithParams(): void
    {
        $range = MediaRange::parse('*/*; q=0.1');

        static::assertSame('*', $range->type);
        static::assertSame('*', $range->subtype);
        static::assertSame(0.1, $range->weight);
        static::assertNull($range->parameters->get('q'));
    }

    public function testEssenceWildcardSubtype(): void
    {
        $range = new MediaRange('text', '*');

        static::assertSame('text/*', $range->essence());
    }

    public function testToStringWithParams(): void
    {
        $range = MediaRange::parse('text/*; q=0.8');

        static::assertSame('text/*; q=0.8', $range->toString());
    }

    public function testFromMediaTypePreservesParams(): void
    {
        $type = MediaType::parse('text/html; charset=utf-8');
        $range = MediaRange::fromMediaType($type);

        static::assertSame('text', $range->type);
        static::assertSame('html', $range->subtype);
        static::assertSame('utf-8', $range->parameters->get('charset'));
    }

    public function testEmptyTypeThrows(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('', '*');
    }

    public function testWeightDefaultsToOne(): void
    {
        $range = new MediaRange('text', 'html');

        static::assertSame(1.0, $range->weight);
    }

    public function testWeightFromConstructor(): void
    {
        $range = new MediaRange('text', 'html', null, 0.5);

        static::assertSame(0.5, $range->weight);
    }

    public function testParseExtractsWeight(): void
    {
        $range = MediaRange::parse('text/html;q=0.9');

        static::assertSame(0.9, $range->weight);
        static::assertNull($range->parameters->get('q'));
    }

    public function testParseWeightDefaultOne(): void
    {
        $range = MediaRange::parse('text/html');

        static::assertSame(1.0, $range->weight);
    }

    public function testParseWeightZero(): void
    {
        $range = MediaRange::parse('text/html;q=0');

        static::assertSame(0.0, $range->weight);
    }

    public function testParseWeightWithOtherParams(): void
    {
        $range = MediaRange::parse('text/html;charset=utf-8;q=0.5');

        static::assertSame(0.5, $range->weight);
        static::assertSame('utf-8', $range->parameters->get('charset'));
        static::assertNull($range->parameters->get('q'));
    }

    public function testWeightAboveOneThrows(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('text', 'html', null, 1.1);
    }

    public function testWeightBelowZeroThrows(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('text', 'html', null, -0.1);
    }

    public function testToStringIncludesWeight(): void
    {
        $range = MediaRange::parse('text/html;q=0.5');

        static::assertSame('text/html; q=0.5', $range->toString());
    }

    public function testToStringOmitsDefaultWeight(): void
    {
        $range = MediaRange::parse('text/html');

        static::assertSame('text/html', $range->toString());
    }

    public function testWeightFormattingStripsTrailingZeros(): void
    {
        $range = MediaRange::parse('text/html;q=0.500');

        static::assertStringContainsString('q=0.5', $range->toString());
        static::assertStringNotContainsString('q=0.500', $range->toString());
    }

    public function testFromMediaTypeWithWeight(): void
    {
        $type = new MediaType('text', 'html');
        $range = MediaRange::fromMediaType($type, 0.8);

        static::assertSame(0.8, $range->weight);
    }

    public function testSpecificityExact(): void
    {
        $range = new MediaRange('text', 'html');

        static::assertSame(3, $range->specificity());
    }

    public function testSpecificityWildcardSubtype(): void
    {
        $range = new MediaRange('text', '*');

        static::assertSame(2, $range->specificity());
    }

    public function testSpecificityFullWildcard(): void
    {
        $range = new MediaRange('*', '*');

        static::assertSame(1, $range->specificity());
    }

    public function testParseNonNumericQThrows(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        MediaRange::parse('text/html;q=abc');
    }

    public function testValidComponentContainingZ(): void
    {
        $range = new MediaRange('text', 'htmlz');

        static::assertSame('htmlz', $range->subtype);
    }

    public function testValidComponentContainingZero(): void
    {
        $range = new MediaRange('text', '0html');

        static::assertSame('0html', $range->subtype);
    }

    public function testValidComponentContainingNine(): void
    {
        $range = new MediaRange('text', 'html9');

        static::assertSame('html9', $range->subtype);
    }

    public function testInvalidCharAfterZThrows(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('text', 'html{');
    }

    public function testInvalidCharBeforeZeroThrows(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('text', 'html/');
    }

    public function testInvalidCharAfterNineThrows(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('text', 'html:');
    }

    public function testWeightExactlyOneIsValid(): void
    {
        $range = new MediaRange('text', 'html', null, 1.0);

        static::assertSame(1.0, $range->weight);
    }

    public function testWeightExactlyZeroIsValid(): void
    {
        $range = new MediaRange('text', 'html', null, 0.0);

        static::assertSame(0.0, $range->weight);
    }

    public function testFromMediaTypeDefaultWeightIsOne(): void
    {
        $type = new MediaType('text', 'html');
        $range = MediaRange::fromMediaType($type);

        static::assertSame(1.0, $range->weight);
    }

    public function testFormatWeightStripsTrailingDot(): void
    {
        $range = new MediaRange('text', 'html', null, 0.0);

        static::assertSame('text/html; q=0', $range->toString());
    }

    public function testParseUppercaseQParameterExtractsWeight(): void
    {
        $range = MediaRange::parse('text/html;Q=0.7');

        static::assertSame(0.7, $range->weight);
    }

    public function testCastFloatInIsValidWeight(): void
    {
        $range = MediaRange::parse('text/html;q=0.123');

        static::assertSame(0.123, $range->weight);
    }

    public function testParseUppercaseQIsRecognized(): void
    {
        $range = MediaRange::parse('text/html; Q=0.7');

        static::assertSame(0.7, $range->weight);
        static::assertNull($range->parameters->get('q'));
        static::assertNull($range->parameters->get('Q'));
    }

    public function testParseInvalidWeightThrows(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        MediaRange::parse('text/html;q=abc');
    }

    public function testParseWeightRoundedToThreeDecimals(): void
    {
        $range = MediaRange::parse('text/html;q=0.1235');

        static::assertSame(0.124, $range->weight);
    }

    public function testParseQParamContinuesProcessing(): void
    {
        $range = MediaRange::parse('text/html;q=0.8;level=1');

        static::assertSame(0.8, $range->weight);
        static::assertSame('1', $range->parameters->get('level'));
    }

    public function testTypeExactly127CharsIsValid(): void
    {
        $type = Str\repeat('a', 127);
        $range = new MediaRange($type, 'html');

        static::assertSame($type, $range->type);
    }

    public function testType128CharsIsRejected(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange(Str\repeat('a', 128), 'html');
    }

    public function testFirstCharOfTypeIsValidated(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('@text', 'html');
    }

    public function testIsValidWeightCastsToFloat(): void
    {
        $range = MediaRange::parse('text/html;q=0.5');

        static::assertSame(0.5, $range->weight);
    }

    public function testWeightExactlyOneIsValidViaIsValidWeight(): void
    {
        $range = MediaRange::parse('text/html;q=1.0');

        static::assertSame(1.0, $range->weight);
    }

    public function testWeightAboveOneIsRejectedViaIsValidWeight(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        MediaRange::parse('text/html;q=1.1');
    }

    public function testWeightNegativeIsRejected(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        MediaRange::parse('text/html;q=-0.5');
    }

    public function testFormatWeightThreeDecimalPrecision(): void
    {
        $range = new MediaRange('text', 'html', null, 0.001);

        $str = $range->toString();
        static::assertStringContainsString('q=0.001', $str);
    }

    public function testFormatWeightPreservesThirdDecimalPlace(): void
    {
        $range = new MediaRange('text', 'html', null, 0.123);

        $str = $range->toString();
        static::assertStringContainsString('q=0.123', $str);
    }

    public function testFormatWeightStripsTrailingZerosCorrectly(): void
    {
        $range = new MediaRange('text', 'html', null, 0.5);

        $str = $range->toString();
        static::assertStringContainsString('q=0.5', $str);
        static::assertStringNotContainsString('q=0.500', $str);
        static::assertStringNotContainsString('q=0.50', $str);
    }

    public function testParseUppercaseQExtractsWeightCaseInsensitive(): void
    {
        $range = MediaRange::parse('text/html;Q=0.8');

        static::assertSame(0.8, $range->weight);
        static::assertNull($range->parameters->get('q'));
        static::assertNull($range->parameters->get('Q'));
    }

    public function testParseInvalidWeightThrowsWithCorrectMessage(): void
    {
        try {
            MediaRange::parse('text/html;q=abc');
            static::fail('Expected exception');
        } catch (InvalidMediaTypeComponentException $e) {
            static::assertStringContainsString('0', $e->getMessage());
        }
    }

    public function testValidateComponentStartsAtIndexZero(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        new MediaRange('@a', 'html');
    }

    public function testIsValidWeightRejectsAboveOne(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        MediaRange::parse('text/html;q=2.0');
    }

    public function testIsValidWeightRejectsBelowZero(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        MediaRange::parse('text/html;q=-1.0');
    }

    public function testFormatWeightUsesThreeDecimalPlaces(): void
    {
        $range = new MediaRange('text', 'html', null, 0.1);
        $str = $range->toString();

        static::assertSame('text/html; q=0.1', $str);
        static::assertStringNotContainsString('q=0.1000', $str);
    }

    public function testIsValidWeightAndOperator(): void
    {
        $this->expectException(InvalidMediaTypeComponentException::class);

        MediaRange::parse('text/html;q=5.0');
    }
}
