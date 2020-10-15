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

        static::assertContains(Password\DEFAULT_ALGORITHM, $algorithms);
        static::assertContains(Password\BCRYPT_ALGORITHM, $algorithms);
        static::assertContains(Password\ARGON2I_ALGORITHM, $algorithms);
        static::assertContains(Password\ARGON2ID_ALGORITHM, $algorithms);

        $algos = Arr\map(
            password_algos(),
            static fn (string $value): string => PASSWORD_BCRYPT === $value ? Password\BCRYPT_ALGORITHM : $value
        );

        static::assertSame($algos, $algorithms);
    }

    /**
     * @dataProvider providePasswords
     */
    public function testBcrypt(string $password): void
    {
        $hash = Password\hash($password, Password\BCRYPT_ALGORITHM, [
            'cost' => 8
        ]);

        static::assertTrue(Password\verify($password, $hash));

        $information = Password\get_information($hash);
        static::assertSame(Password\BCRYPT_ALGORITHM, $information['algorithm']);
        static::assertSame(8, $information['options']['cost']);

        static::assertFalse(Password\needs_rehash($hash, Password\BCRYPT_ALGORITHM, [
            'cost' => 8
        ]));
    }

    /**
     * @dataProvider providePasswords
     */
    public function testArgon2i(string $password): void
    {
        $hash = Password\hash($password, Password\ARGON2I_ALGORITHM);

        static::assertTrue(Password\verify($password, $hash));

        $information = Password\get_information($hash);
        static::assertSame(Password\ARGON2I_ALGORITHM, $information['algorithm']);
        static::assertSame(Password\ARGON2_DEFAULT_MEMORY_COST, $information['options']['memory_cost']);
        static::assertSame(Password\ARGON2_DEFAULT_TIME_COST, $information['options']['time_cost']);
        static::assertSame(Password\ARGON2_DEFAULT_THREADS, $information['options']['threads']);

        static::assertFalse(Password\needs_rehash($hash, Password\ARGON2I_ALGORITHM));
    }

    public function providePasswords(): iterable
    {
        yield ['hunter2'];
        yield [SecureRandom\string(64)];
        yield [SecureRandom\string(32, Str\ALPHABET_ALPHANUMERIC . '&"\'{([-|`\_^@])}=+£¨%!/;.,:')];
        yield [SecureRandom\string(16, Str\ALPHABET_ALPHANUMERIC . '&"\'{([-|`\_^@])}=+£¨%!/;.,:')];
    }
}
