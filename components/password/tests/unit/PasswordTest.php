<?php

declare(strict_types=1);

namespace Psl\Password\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Password;
use Psl\SecureRandom;
use Psl\Str;
use SensitiveParameter;

final class PasswordTest extends TestCase
{
    #[DataProvider('providePasswords')]
    public function testDefault(#[SensitiveParameter] string $password): void
    {
        $hash = Password\hash($password, Password\Algorithm::default(), [
            'cost' => 4,
        ]);

        static::assertTrue(Password\verify($password, $hash));

        static::assertFalse(Password\needs_rehash($hash, Password\Algorithm::default(), [
            'cost' => 4,
        ]));
    }

    #[DataProvider('providePasswords')]
    public function testBcrypt(#[SensitiveParameter] string $password): void
    {
        $hash = Password\hash($password, Password\Algorithm::Bcrypt, [
            'cost' => 4,
        ]);

        static::assertTrue(Password\verify($password, $hash));

        $information = Password\get_information($hash);
        static::assertSame(Password\Algorithm::Bcrypt, $information['algorithm']);
        static::assertSame(4, $information['options']['cost']);

        static::assertFalse(Password\needs_rehash($hash, Password\Algorithm::Bcrypt, [
            'cost' => 4,
        ]));
    }

    #[DataProvider('providePasswords')]
    public function testArgon2i(#[SensitiveParameter] string $password): void
    {
        $options = ['memory_cost' => 8192];
        $hash = Password\hash($password, Password\Algorithm::Argon2i, $options);

        static::assertTrue(Password\verify($password, $hash));

        $information = Password\get_information($hash);

        static::assertSame(Password\Algorithm::Argon2i, $information['algorithm']);

        static::assertFalse(Password\needs_rehash($hash, Password\Algorithm::Argon2i, $options));
    }

    #[DataProvider('providePasswords')]
    public function testArgon2id(#[SensitiveParameter] string $password): void
    {
        $options = ['memory_cost' => 8192];
        $hash = Password\hash($password, Password\Algorithm::Argon2id, $options);

        static::assertTrue(Password\verify($password, $hash));

        $information = Password\get_information($hash);

        static::assertSame(Password\Algorithm::Argon2id, $information['algorithm']);

        static::assertFalse(Password\needs_rehash($hash, Password\Algorithm::Argon2id, $options));
    }

    public function testArgon2idBuiltinConstantValue(): void
    {
        static::assertSame(PASSWORD_ARGON2ID, Password\Algorithm::Argon2id->getBuiltinConstantValue());
    }

    public function testAllAlgorithmBuiltinConstantValues(): void
    {
        static::assertSame(PASSWORD_DEFAULT, Password\Algorithm::Default->getBuiltinConstantValue());
        static::assertSame(PASSWORD_BCRYPT, Password\Algorithm::Bcrypt->getBuiltinConstantValue());
        static::assertSame(PASSWORD_ARGON2I, Password\Algorithm::Argon2i->getBuiltinConstantValue());
        static::assertSame(PASSWORD_ARGON2ID, Password\Algorithm::Argon2id->getBuiltinConstantValue());
    }

    public static function providePasswords(): iterable
    {
        yield ['hunter2'];
        yield [SecureRandom\string(64)];
        yield [SecureRandom\string(32, Str\ALPHABET_ALPHANUMERIC . '&"\'{([-|`\_^@])}=+£¨%!/;.,:')];
        yield [SecureRandom\string(16, Str\ALPHABET_ALPHANUMERIC . '&"\'{([-|`\_^@])}=+£¨%!/;.,:')];
    }
}
