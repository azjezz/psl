<?php

declare(strict_types=1);

namespace Psl\Locale\Tests\Unit;

use Generator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Locale\Locale;
use Psl\Str;

use function locale_get_default;
use function locale_set_default;

final class LocaleTest extends TestCase
{
    private null|string $defaultLocale = null;

    #[Override]
    protected function setUp(): void
    {
        $this->defaultLocale = locale_get_default();
    }

    #[Override]
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
    public static function getAllLocales(): Generator
    {
        foreach (Locale::cases() as $locale) {
            yield $locale->value => [$locale];
        }
    }

    #[DataProvider('getAllLocales')]
    public function testItReturnsTheLanguageAndHumanReadableName(Locale $locale): void
    {
        $displayLanguage = $locale->getDisplayLanguage(Locale::English);
        $language = $locale->getLanguage();
        $displayName = $locale->getDisplayName(Locale::English);

        static::assertNotEmpty($displayLanguage);
        static::assertStringContainsString($language, $locale->value);

        static::assertStringContainsString($displayLanguage, $displayName);
        if ($locale->hasRegion()) {
            $region = $locale->getDisplayRegion(Locale::English);
            $region = Str\replace_every($region, [
                '(' => '[',
                ')' => ']',
            ]);

            static::assertStringContainsString($region, $displayName);
        }
    }

    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public static function getLocalesWithScript(): Generator
    {
        foreach (Locale::cases() as $locale) {
            if (!$locale->hasScript()) {
                continue;
            }

            yield $locale->value => [$locale];
        }
    }

    #[DataProvider('getLocalesWithScript')]
    public function testItReturnsTheScript(Locale $locale): void
    {
        static::assertTrue($locale->hasScript());
        static::assertNotEmpty($locale->getScript());
    }

    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public static function getLocalesWithoutScript(): Generator
    {
        foreach (Locale::cases() as $locale) {
            if ($locale->hasScript()) {
                continue;
            }

            yield $locale->value => [$locale];
        }
    }

    #[DataProvider('getLocalesWithoutScript')]
    public function testItDoesNotReturnsTheScript(Locale $locale): void
    {
        static::assertFalse($locale->hasScript());
        static::assertNull($locale->getScript());
    }

    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public static function getLocalesWithRegion(): Generator
    {
        foreach (Locale::cases() as $locale) {
            if (!$locale->hasRegion()) {
                continue;
            }

            yield $locale->value => [$locale];
        }
    }

    #[DataProvider('getLocalesWithRegion')]
    public function testItReturnsTheRegion(Locale $locale): void
    {
        static::assertTrue($locale->hasRegion());
        static::assertNotEmpty($locale->getRegion());
        static::assertNotEmpty($locale->getDisplayRegion());
    }

    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public static function getLocalesWithoutRegion(): Generator
    {
        foreach (Locale::cases() as $locale) {
            if ($locale->hasRegion()) {
                continue;
            }

            yield $locale->value => [$locale];
        }
    }

    #[DataProvider('getLocalesWithoutRegion')]
    public function testItDoesNotReturnsTheRegion(Locale $locale): void
    {
        static::assertFalse($locale->hasRegion());
        static::assertNull($locale->getRegion());
        static::assertNull($locale->getDisplayRegion());
    }

    public function testDefaultWithLanguageOnlyLocale(): void
    {
        locale_set_default('fr');
        $locale = Locale::default();
        static::assertSame(Locale::French, $locale);
        static::assertSame('fr', $locale->getLanguage());
    }

    public function testDefaultWithScriptLocale(): void
    {
        locale_set_default('sr_Cyrl');
        $locale = Locale::default();
        static::assertSame(Locale::SerbianCyrillic, $locale);

        locale_set_default('sr_Latn');
        $locale = Locale::default();
        static::assertSame(Locale::SerbianLatin, $locale);
    }

    public function testDefaultWithRegionLocale(): void
    {
        locale_set_default('en_US');
        $locale = Locale::default();
        static::assertSame(Locale::EnglishUnitedStates, $locale);

        locale_set_default('fr_FR');
        $locale = Locale::default();
        static::assertSame(Locale::FrenchFrance, $locale);
    }

    public function testDefaultWithScriptAndRegionLocale(): void
    {
        locale_set_default('sr_Cyrl_RS');
        $locale = Locale::default();
        static::assertSame(Locale::SerbianCyrillicSerbia, $locale);

        locale_set_default('sr_Latn_RS');
        $locale = Locale::default();
        static::assertSame(Locale::SerbianLatinSerbia, $locale);
    }

    public function testLanguageIsLowercased(): void
    {
        locale_set_default('EN_US');
        $locale = Locale::default();
        static::assertSame(Locale::EnglishUnitedStates, $locale);

        locale_set_default('FR_FR');
        $locale = Locale::default();
        static::assertSame(Locale::FrenchFrance, $locale);
    }

    public function testScriptIsUcfirsted(): void
    {
        locale_set_default('sr_cyrl');
        $locale = Locale::default();
        static::assertSame(Locale::SerbianCyrillic, $locale);

        locale_set_default('sr_latn');
        $locale = Locale::default();
        static::assertSame(Locale::SerbianLatin, $locale);

        // Also test with region
        locale_set_default('sr_cyrl_RS');
        $locale = Locale::default();
        static::assertSame(Locale::SerbianCyrillicSerbia, $locale);
    }

    public function testRegionIsUppercased(): void
    {
        locale_set_default('en_us');
        $locale = Locale::default();
        static::assertSame(Locale::EnglishUnitedStates, $locale);

        locale_set_default('fr_fr');
        $locale = Locale::default();
        static::assertSame(Locale::FrenchFrance, $locale);

        locale_set_default('ar_tn');
        $locale = Locale::default();
        static::assertSame(Locale::ArabicTunisia, $locale);
    }

    public function testLanguageLowercaseIsRequired(): void
    {
        locale_set_default('EN_US');
        static::assertSame(Locale::EnglishUnitedStates, Locale::default());

        locale_set_default('FR');
        static::assertSame(Locale::French, Locale::default());
    }

    public function testScriptUcfirstIsRequired(): void
    {
        locale_set_default('sr_cyrl_RS');
        static::assertSame(Locale::SerbianCyrillicSerbia, Locale::default());

        locale_set_default('sr_CYRL');
        static::assertSame(Locale::SerbianCyrillic, Locale::default());

        locale_set_default('sr_LATN_RS');
        static::assertSame(Locale::SerbianLatinSerbia, Locale::default());
    }

    public function testRegionUppercaseIsRequired(): void
    {
        locale_set_default('en_us');
        static::assertSame(Locale::EnglishUnitedStates, Locale::default());

        locale_set_default('fr_fr');
        static::assertSame(Locale::FrenchFrance, Locale::default());

        locale_set_default('ar_tn');
        static::assertSame(Locale::ArabicTunisia, Locale::default());

        locale_set_default('de_de');
        static::assertSame(Locale::GermanGermany, Locale::default());
    }
}
