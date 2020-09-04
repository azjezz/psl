<?php

declare(strict_types=1);

namespace Psl\Tests\Password;

use PHPUnit\Framework\TestCase;
use Psl\Arr;
use Psl\Password;
use Psl\SecureRandom;
use Psl\Str;
use const PASSWORD_BCRYPT;

final class PasswordTest extends TestCase
{
    public function testAlgorithms(): void
    {
        $algorithms = Password\algorithms();

        self::assertContains(Password\DEFAULT_ALGORITHM, $algorithms);
        self::assertContains(Password\BCRYPT_ALGORITHM, $algorithms);
        self::assertContains(Password\ARGON2I_ALGORITHM, $algorithms);
        self::assertContains(Password\ARGON2ID_ALGORITHM, $algorithms);

        $algos = Arr\map(password_algos(), fn(string $value): string => PASSWORD_BCRYPT === $value ? Password\BCRYPT_ALGORITHM : $value);

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

    /**
     * @dataProvider providePasswords
     */
    public function testArgon2i(string $password): void
    {
        $hash = Password\hash($password, Password\ARGON2I_ALGORITHM);

        self::assertTrue(Password\verify($password, $hash));

        $information = Password\get_information($hash);
        self::assertSame(Password\ARGON2I_ALGORITHM, $information['algorithm']);
        self::assertSame(Password\ARGON2_DEFAULT_MEMORY_COST, $information['options']['memory_cost']);
        self::assertSame(Password\ARGON2_DEFAULT_TIME_COST, $information['options']['time_cost']);
        self::assertSame(Password\ARGON2_DEFAULT_THREADS, $information['options']['threads']);

        self::assertFalse(Password\needs_rehash($hash, Password\ARGON2I_ALGORITHM));
    }

    public function providePasswords(): iterable
    {
        yield ['hunter2'];
        yield [SecureRandom\string(64)];
        yield [SecureRandom\string(32, Str\ALPHABET_ALPHANUMERIC . '&"\'{([-|`\_^@])}=+£¨%!/;.,:')];
        yield [SecureRandom\string(16, Str\ALPHABET_ALPHANUMERIC . '&"\'{([-|`\_^@])}=+£¨%!/;.,:')];
    }
}
