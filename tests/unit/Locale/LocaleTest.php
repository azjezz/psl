<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Locale;

use Generator;
use PHPUnit\Framework\TestCase;
use Psl\Locale\Locale;
use Psl\Str;

use function locale_get_default;
use function locale_set_default;

final class LocaleTest extends TestCase
{
    private null|string $defaultLocale = null;
    protected function setUp(): void
    {
        $this->defaultLocale = locale_get_default();
    }

    protected function tearDown(): void
    {
        if (null !== $this->defaultLocale) {
            locale_set_default($this->defaultLocale);
        }
    }

    public function testDefault(): void
    {
        foreach (Locale::cases() as $locale) {
            locale_set_default($locale->value);

            static::assertSame($locale, Locale::default());
        }
    }

    public function testDefaultIgnoresCharset(): void
    {
        locale_set_default('sr_RS.UTF-8');
        static::assertSame(Locale::SerbianSerbia, Locale::default());

        locale_set_default('sr_Cyrl.UTF-8');
        static::assertSame(Locale::SerbianCyrillic, Locale::default());
        locale_set_default('sr_Cyrl_RS.UTF-8');
        static::assertSame(Locale::SerbianCyrillicSerbia, Locale::default());

        locale_set_default('sr_Latn.UTF-8');
        static::assertSame(Locale::SerbianLatin, Locale::default());
        locale_set_default('sr_Latn_RS.UTF-8');
        static::assertSame(Locale::SerbianLatinSerbia, Locale::default());
    }

    public function testDefaultIgnoresVariant(): void
    {
        locale_set_default('sr_RS@ekavsk');
        static::assertSame(Locale::SerbianSerbia, Locale::default());

        locale_set_default('sr_Cyrl@ekavsk');
        static::assertSame(Locale::SerbianCyrillic, Locale::default());
        locale_set_default('sr_Cyrl_RS@ekavsk');
        static::assertSame(Locale::SerbianCyrillicSerbia, Locale::default());

        locale_set_default('sr_Latn@ekavsk');
        static::assertSame(Locale::SerbianLatin, Locale::default());
        locale_set_default('sr_Latn_RS@ekavsk');
        static::assertSame(Locale::SerbianLatinSerbia, Locale::default());
    }

    public function testDefaultIgnoresExtension(): void
    {
        locale_set_default('sr_RS-u-currency-EUR');
        static::assertSame(Locale::SerbianSerbia, Locale::default());

        locale_set_default('sr_Cyrl-u-currency-EUR');
        static::assertSame(Locale::SerbianCyrillic, Locale::default());
        locale_set_default('sr_Cyrl_RS-u-currency-EUR');
        static::assertSame(Locale::SerbianCyrillicSerbia, Locale::default());

        locale_set_default('sr_Latn-u-currency-EUR');
        static::assertSame(Locale::SerbianLatin, Locale::default());
        locale_set_default('sr_Latn_RS-u-currency-EUR');
        static::assertSame(Locale::SerbianLatinSerbia, Locale::default());
    }

    public function testDefaultIgnoresCasing(): void
    {
        locale_set_default('ar_TN');
        static::assertSame(Locale::ArabicTunisia, Locale::default());

        locale_set_default('AR_TN');
        static::assertSame(Locale::ArabicTunisia, Locale::default());

        locale_set_default('AR_tn');
        static::assertSame(Locale::ArabicTunisia, Locale::default());

        locale_set_default('aR_Tn');
        static::assertSame(Locale::ArabicTunisia, Locale::default());

        locale_set_default('Ar_tN');
        static::assertSame(Locale::ArabicTunisia, Locale::default());

        locale_set_default('Ar_TN');
        static::assertSame(Locale::ArabicTunisia, Locale::default());

        locale_set_default('aR_TN');
        static::assertSame(Locale::ArabicTunisia, Locale::default());

        locale_set_default('AR_Tn');
        static::assertSame(Locale::ArabicTunisia, Locale::default());

        locale_set_default('AR_tN');
        static::assertSame(Locale::ArabicTunisia, Locale::default());
    }

    public function testFallbackToJustLanguage(): void
    {
        locale_set_default('zh_CN');

        static::assertSame(Locale::Chinese, Locale::default());
    }

    public function testDefaultFallbacksToEnglish(): void
    {
        locale_set_default('xx_XX');

        static::assertSame(Locale::English, Locale::default());
    }

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
