<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\MIME\MediaPreferences;
use Psl\MIME\MediaRange;
use Psl\MIME\MediaType;

final class MediaPreferencesTest extends TestCase
{
    public function testParseSimple(): void
    {
        $prefs = MediaPreferences::parse('text/html');

        static::assertCount(1, $prefs->ranges);
        static::assertSame('text/html', $prefs->ranges[0]->essence());
        static::assertSame(1.0, $prefs->ranges[0]->weight);
    }

    public function testParseMultiple(): void
    {
        $prefs = MediaPreferences::parse('text/html, application/json');

        static::assertCount(2, $prefs->ranges);
        static::assertSame(1.0, $prefs->ranges[0]->weight);
        static::assertSame(1.0, $prefs->ranges[1]->weight);
    }

    public function testParseWithWeights(): void
    {
        $prefs = MediaPreferences::parse('text/html, application/json;q=0.9, */*;q=0.1');

        static::assertCount(3, $prefs->ranges);
        static::assertSame('text/html', $prefs->ranges[0]->essence());
        static::assertSame(1.0, $prefs->ranges[0]->weight);
        static::assertSame('application/json', $prefs->ranges[1]->essence());
        static::assertSame(0.9, $prefs->ranges[1]->weight);
        static::assertSame('*/*', $prefs->ranges[2]->essence());
        static::assertSame(0.1, $prefs->ranges[2]->weight);
    }

    public function testParseSingle(): void
    {
        $prefs = MediaPreferences::parse('*/*');

        static::assertCount(1, $prefs->ranges);
        static::assertSame('*/*', $prefs->ranges[0]->essence());
    }

    public function testParseEmpty(): void
    {
        $prefs = MediaPreferences::parse('');

        static::assertCount(0, $prefs->ranges);
    }

    public function testParseTrimsWhitespace(): void
    {
        $prefs = MediaPreferences::parse(' text/html , application/json ');

        static::assertCount(2, $prefs->ranges);
        static::assertSame('text/html', $prefs->ranges[0]->essence());
        static::assertSame('application/json', $prefs->ranges[1]->essence());
    }

    public function testRangesSortedByWeight(): void
    {
        $prefs = MediaPreferences::parse('application/xml;q=0.5, text/html;q=0.9, application/json;q=0.7');

        static::assertSame('text/html', $prefs->ranges[0]->essence());
        static::assertSame('application/json', $prefs->ranges[1]->essence());
        static::assertSame('application/xml', $prefs->ranges[2]->essence());
    }

    public function testRangesSortedBySpecificity(): void
    {
        $prefs = MediaPreferences::parse('*/*,text/*,text/html');

        static::assertSame('text/html', $prefs->ranges[0]->essence());
        static::assertSame('text/*', $prefs->ranges[1]->essence());
        static::assertSame('*/*', $prefs->ranges[2]->essence());
    }

    public function testBestForExactMatch(): void
    {
        $prefs = MediaPreferences::parse('text/html, application/json');
        $type = new MediaType('application', 'json');

        $best = $prefs->bestFor($type);

        static::assertNotNull($best);
        static::assertSame('application/json', $best->essence());
    }

    public function testBestForWildcardMatch(): void
    {
        $prefs = MediaPreferences::parse('text/*, application/json');
        $type = new MediaType('text', 'plain');

        $best = $prefs->bestFor($type);

        static::assertNotNull($best);
        static::assertSame('text/*', $best->essence());
    }

    public function testBestForNoMatch(): void
    {
        $prefs = MediaPreferences::parse('text/html');
        $type = new MediaType('image', 'png');

        $best = $prefs->bestFor($type);

        static::assertNull($best);
    }

    public function testBestForPrefersHigherWeight(): void
    {
        $prefs = MediaPreferences::parse('text/*;q=0.5, */*;q=0.9');
        $type = new MediaType('text', 'html');

        $best = $prefs->bestFor($type);

        static::assertNotNull($best);
        static::assertSame('*/*', $best->essence());
    }

    public function testNegotiatePicksBest(): void
    {
        $prefs = MediaPreferences::parse('text/html, application/json;q=0.9');

        $available = [
            new MediaType('application', 'json'),
            new MediaType('text', 'html'),
        ];

        $result = $prefs->negotiate($available);

        static::assertNotNull($result);
        static::assertSame('text/html', $result->essence());
    }

    public function testNegotiateReturnsNull(): void
    {
        $prefs = MediaPreferences::parse('text/html');

        $available = [
            new MediaType('image', 'png'),
        ];

        $result = $prefs->negotiate($available);

        static::assertNull($result);
    }

    public function testNegotiateSkipsZeroWeight(): void
    {
        $prefs = MediaPreferences::parse('text/html;q=0, application/json');

        $available = [
            new MediaType('text', 'html'),
            new MediaType('application', 'json'),
        ];

        $result = $prefs->negotiate($available);

        static::assertNotNull($result);
        static::assertSame('application/json', $result->essence());
    }

    public function testNegotiateSpecificityTiebreak(): void
    {
        $prefs = MediaPreferences::parse('text/html, text/*');

        $available = [
            new MediaType('text', 'plain'),
            new MediaType('text', 'html'),
        ];

        $result = $prefs->negotiate($available);

        static::assertNotNull($result);
        static::assertSame('text/html', $result->essence());
    }

    public function testCountable(): void
    {
        $prefs = MediaPreferences::parse('text/html, application/json, */*');

        static::assertCount(3, $prefs);
    }

    public function testIteratorAggregate(): void
    {
        $prefs = MediaPreferences::parse('text/html, application/json');

        $ranges = [];
        foreach ($prefs as $range) {
            static::assertInstanceOf(MediaRange::class, $range);
            $ranges[] = $range;
        }

        static::assertCount(2, $ranges);
    }

    public function testStringable(): void
    {
        $prefs = MediaPreferences::parse('text/html, application/json');

        static::assertSame($prefs->toString(), (string) $prefs);
    }

    public function testToStringRoundTrip(): void
    {
        $prefs = MediaPreferences::parse('text/html, application/json;q=0.9, */*;q=0.1');

        $string = $prefs->toString();

        static::assertSame('text/html, application/json; q=0.9, */*; q=0.1', $string);
    }

    public function testFrom(): void
    {
        $range1 = new MediaRange('text', 'html', weight: 0.8);
        $range2 = new MediaRange('application', 'json', weight: 1.0);

        $prefs = MediaPreferences::from($range1, $range2);

        static::assertCount(2, $prefs->ranges);
        static::assertSame('application/json', $prefs->ranges[0]->essence());
        static::assertSame('text/html', $prefs->ranges[1]->essence());
    }

    public function testParseQuotedComma(): void
    {
        $prefs = MediaPreferences::parse('text/html; boundary="hello,world", application/json');

        static::assertCount(2, $prefs->ranges);
        static::assertSame('text/html', $prefs->ranges[0]->essence());
        static::assertSame('hello,world', $prefs->ranges[0]->parameters->get('boundary'));
        static::assertSame('application/json', $prefs->ranges[1]->essence());
    }

    public function testParseMultipleQuotedCommas(): void
    {
        $prefs = MediaPreferences::parse('text/html; boundary="a,b,c", application/json; name="x,y"');

        static::assertCount(2, $prefs->ranges);
        static::assertSame('a,b,c', $prefs->ranges[0]->parameters->get('boundary'));
        static::assertSame('x,y', $prefs->ranges[1]->parameters->get('name'));
    }

    public function testParseWhitespaceOnlyIsEmpty(): void
    {
        $prefs = MediaPreferences::parse('   ');

        static::assertCount(0, $prefs->ranges);
    }

    public function testToStringReturnsMediaRangeStrings(): void
    {
        $prefs = MediaPreferences::parse('text/html');

        static::assertSame('text/html', $prefs->toString());
    }

    public function testBestForPrefersMoreSpecificAtSameWeight(): void
    {
        $prefs = MediaPreferences::from(new MediaRange('*', '*'), new MediaRange('text', 'html'));

        $type = new MediaType('text', 'html');
        $best = $prefs->bestFor($type);

        static::assertNotNull($best);
        static::assertSame('text/html', $best->essence());
    }

    public function testNegotiateWithEqualWeightPrefersMoreSpecific(): void
    {
        $prefs = MediaPreferences::parse('text/*, text/html');

        $available = [
            new MediaType('text', 'plain'),
        ];

        $result = $prefs->negotiate($available);

        static::assertNotNull($result);
        static::assertSame('text/plain', $result->essence());
    }
}
