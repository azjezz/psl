<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\SMTP\Reply;

final class ReplyCodeCategoryCombinationsTest extends TestCase
{
    #[DataProvider('firstDigitProvider')]
    public function testFirstDigitCategories(
        int $code,
        bool $posComp,
        bool $posInt,
        bool $transNeg,
        bool $permNeg,
    ): void {
        $response = new Reply($code, null, 'Test');

        static::assertSame($posComp, $response->isPositiveCompletion());
        static::assertSame($posInt, $response->isPositiveIntermediate());
        static::assertSame($transNeg, $response->isTransientNegativeCompletion());
        static::assertSame($permNeg, $response->isPermanentNegativeCompletion());
    }

    /**
     * @return iterable<string, array{int, bool, bool, bool, bool}>
     */
    public static function firstDigitProvider(): iterable
    {
        yield '200' => [200, true, false, false, false];
        yield '211' => [211, true, false, false, false];
        yield '220' => [220, true, false, false, false];
        yield '235' => [235, true, false, false, false];
        yield '250' => [250, true, false, false, false];
        yield '251' => [251, true, false, false, false];
        yield '252' => [252, true, false, false, false];
        yield '299' => [299, true, false, false, false];
        yield '300' => [300, false, true, false, false];
        yield '334' => [334, false, true, false, false];
        yield '354' => [354, false, true, false, false];
        yield '399' => [399, false, true, false, false];
        yield '400' => [400, false, false, true, false];
        yield '421' => [421, false, false, true, false];
        yield '450' => [450, false, false, true, false];
        yield '451' => [451, false, false, true, false];
        yield '452' => [452, false, false, true, false];
        yield '499' => [499, false, false, true, false];
        yield '500' => [500, false, false, false, true];
        yield '501' => [501, false, false, false, true];
        yield '502' => [502, false, false, false, true];
        yield '503' => [503, false, false, false, true];
        yield '504' => [504, false, false, false, true];
        yield '535' => [535, false, false, false, true];
        yield '550' => [550, false, false, false, true];
        yield '551' => [551, false, false, false, true];
        yield '552' => [552, false, false, false, true];
        yield '553' => [553, false, false, false, true];
        yield '554' => [554, false, false, false, true];
        yield '599' => [599, false, false, false, true];
    }

    #[DataProvider('secondDigitProvider')]
    public function testSecondDigitCategories(
        int $code,
        bool $syntax,
        bool $info,
        bool $conn,
        bool $unspec,
        bool $mail,
    ): void {
        $response = new Reply($code, null, 'Test');

        static::assertSame($syntax, $response->isSyntaxCategory());
        static::assertSame($info, $response->isInformationCategory());
        static::assertSame($conn, $response->isConnectionsCategory());
        static::assertSame($unspec, $response->isUnspecifiedCategory());
        static::assertSame($mail, $response->isMailSystemCategory());
    }

    /**
     * @return iterable<string, array{int, bool, bool, bool, bool, bool}>
     */
    public static function secondDigitProvider(): iterable
    {
        // x0z - syntax
        yield '200' => [200, true, false, false, false, false];
        yield '500' => [500, true, false, false, false, false];
        yield '400' => [400, true, false, false, false, false];
        yield '300' => [300, true, false, false, false, false];
        yield '502' => [502, true, false, false, false, false];

        // x1z - information
        yield '211' => [211, false, true, false, false, false];
        yield '214' => [214, false, true, false, false, false];
        yield '510' => [510, false, true, false, false, false];

        // x2z - connections
        yield '220' => [220, false, false, true, false, false];
        yield '221' => [221, false, false, true, false, false];
        yield '421' => [421, false, false, true, false, false];

        // x3z - unspecified
        yield '334' => [334, false, false, false, true, false];
        yield '530' => [530, false, false, false, true, false];

        // x4z - unspecified
        yield '443' => [443, false, false, false, true, false];
        yield '440' => [440, false, false, false, true, false];

        // x5z - mail system
        yield '250' => [250, false, false, false, false, true];
        yield '251' => [251, false, false, false, false, true];
        yield '354' => [354, false, false, false, false, true];
        yield '450' => [450, false, false, false, false, true];
        yield '550' => [550, false, false, false, false, true];
        yield '551' => [551, false, false, false, false, true];
        yield '552' => [552, false, false, false, false, true];
        yield '553' => [553, false, false, false, false, true];
        yield '554' => [554, false, false, false, false, true];
    }
}
