<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Fixture;

final class ClassWithConstants
{
    public const string FOO = 'foo';
    public const string BAR = 'bar';
    public const int BAZ = 42;
    public const bool QUX = true;
    protected const string PROTECTED_CONST = 'protected';
    private const string PRIVATE_CONST = 'private';
}
