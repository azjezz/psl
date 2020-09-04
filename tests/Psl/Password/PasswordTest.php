<?php

declare(strict_types=1);

namespace Psl\Tests\Password;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Password;
use Psl\SecureRandom;
use Psl\Str;

final class PasswordTest extends TestCase
{
    public function testAlgorithms(): void
    {
        $algorithms = Password\algorithms();

        self::assertContains(Password\DEFAULT_ALGORITHM, $algorithms);
        self::assertContains(Password\BCRYPT_ALGORITHM, $algorithms);
        if (defined('Psl\Password\ARGON2I_ALGORITHM')) {
            self::assertContains(Password\ARGON2I_ALGORITHM, $algorithms);
        }

        if (defined('Psl\Password\ARGON2ID_ALGORITHM')) {
            self::assertContains(Password\ARGON2ID_ALGORITHM, $algorithms);
        }

        $algos = Arr\map(password_algos(), fn (string $value): string => \PASSWORD_BCRYPT === $value ? Password\BCRYPT_ALGORITHM : $value);

        self::assertSame($algos, $algorithms);
    }

    /**
     * @dataProvider providePasswords
     */
    public function testBcrypt(string $password): void
    {
        $hash = Password\hash($password, Password\BCRYPT_ALGORITHM, [
            'cost' => 8
        ]);

        self::assertTrue(Password\verify($password, $hash));

        $information = Password\get_information($hash);
        self::assertSame(Password\BCRYPT_ALGORITHM, $information['algorithm']);
        self::assertSame(8, $information['options']['cost']);

        self::assertFalse(Password\needs_rehash($hash, Password\BCRYPT_ALGORITHM, [
            'cost' => 8
        ]));
    }

    public function providePasswords(): iterable
    {
        yield ['hunter2'];
        yield [SecureRandom\string(64)];
        yield [SecureRandom\string(32, Str\ALPHABET_ALPHANUMERIC . '&"\'{([-|`\_^@])}=+£¨%!/;.,:')];
        yield [SecureRandom\string(16, Str\ALPHABET_ALPHANUMERIC . '&"\'{([-|`\_^@])}=+£¨%!/;.,:')];
    }
}
