<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Password;

use PHPUnit\Framework\TestCase;
use Psl\Password;
use Psl\SecureRandom;
use Psl\Str;

final class PasswordTest extends TestCase
{
    /**
     * @dataProvider providePasswords
     */
    public function testDefault(string $password): void
    {
        $hash = Password\hash($password, Password\Algorithm::default());

        static::assertTrue(Password\verify($password, $hash));

        static::assertFalse(Password\needs_rehash($hash, Password\Algorithm::default()));
    }

    /**
     * @dataProvider providePasswords
     */
    public function testBcrypt(string $password): void
    {
        $hash = Password\hash($password, Password\Algorithm::Bcrypt, [
            'cost' => 8,
        ]);

        static::assertTrue(Password\verify($password, $hash));

        $information = Password\get_information($hash);
        static::assertSame(Password\Algorithm::Bcrypt, $information['algorithm']);
        static::assertSame(8, $information['options']['cost']);

        static::assertFalse(Password\needs_rehash($hash, Password\Algorithm::Bcrypt, [
            'cost' => 8,
        ]));
    }

    /**
     * @dataProvider providePasswords
     */
    public function testArgon2i(string $password): void
    {
        $hash = Password\hash($password, Password\Algorithm::Argon2i);

        static::assertTrue(Password\verify($password, $hash));

        $information = Password\get_information($hash);

        static::assertSame(Password\Algorithm::Argon2i, $information['algorithm']);

        static::assertFalse(Password\needs_rehash($hash, Password\Algorithm::Argon2i));
    }

    public function providePasswords(): iterable
    {
        yield ['hunter2'];
        yield [SecureRandom\string(64)];
        yield [SecureRandom\string(32, Str\ALPHABET_ALPHANUMERIC . '&"\'{([-|`\_^@])}=+£¨%!/;.,:')];
        yield [SecureRandom\string(16, Str\ALPHABET_ALPHANUMERIC . '&"\'{([-|`\_^@])}=+£¨%!/;.,:')];
    }
}
