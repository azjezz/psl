<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Locale;

use Generator;
use PHPUnit\Framework\TestCase;
use Psl\Locale\Locale;
use Psl\Str;

final class LocaleTest extends TestCase
{
    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public function getAllLocales(): Generator
    {
        foreach (Locale::cases() as $locale) {
            yield $locale->value => [$locale];
        }
    }

    /**
     * @dataProvider getAllLocales
     */
    public function testItReturnsTheLanguageAndHumanReadableName(Locale $locale): void
    {
        $display_language = $locale->getDisplayLanguage(Locale::English);
        $language = $locale->getLanguage();
        $display_name = $locale->getDisplayName(Locale::English);

        static::assertNotEmpty($display_language);
        static::assertStringContainsString($language, $locale->value);

        static::assertStringContainsString($display_language, $display_name);
        if ($locale->hasRegion()) {
            $region = $locale->getDisplayRegion(Locale::English);
            $region = Str\replace_every($region, [
                '(' => '[',
                ')' => ']',
            ]);

            static::assertStringContainsString($region, $display_name);
        }
    }

    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public function getLocalesWithScript(): Generator
    {
        foreach (Locale::cases() as $locale) {
            if ($locale->hasScript()) {
                yield $locale->value => [$locale];
            }
        }
    }

    /**
     * @dataProvider getLocalesWithScript
     */
    public function testItReturnsTheScript(Locale $locale): void
    {
        static::assertTrue($locale->hasScript());
        static::assertNotEmpty($locale->getScript());
    }

    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public function getLocalesWithoutScript(): Generator
    {
        foreach (Locale::cases() as $locale) {
            if (!$locale->hasScript()) {
                yield $locale->value => [$locale];
            }
        }
    }
    /**
     * @dataProvider getLocalesWithoutScript
     */
    public function testItDoesNotReturnsTheScript(Locale $locale): void
    {
        static::assertFalse($locale->hasScript());
        static::assertNull($locale->getScript());
    }

    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public function getLocalesWithRegion(): Generator
    {
        foreach (Locale::cases() as $locale) {
            if ($locale->hasRegion()) {
                yield $locale->value => [$locale];
            }
        }
    }

    /**
     * @dataProvider getLocalesWithRegion
     */
    public function testItReturnsTheRegion(Locale $locale): void
    {
        static::assertTrue($locale->hasRegion());
        static::assertNotEmpty($locale->getRegion());
        static::assertNotEmpty($locale->getDisplayRegion());
    }

    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public function getLocalesWithoutRegion(): Generator
    {
        foreach (Locale::cases() as $locale) {
            if (!$locale->hasRegion()) {
                yield $locale->value => [$locale];
            }
        }
    }
    /**
     * @dataProvider getLocalesWithoutRegion
     */
    public function testItDoesNotReturnsTheRegion(Locale $locale): void
    {
        static::assertFalse($locale->hasRegion());
        static::assertNull($locale->getRegion());
        static::assertNull($locale->getDisplayRegion());
    }
}
