<?php

declare(strict_types=1);

namespace Psl\Tests\Fixture;

final class ClassWithProperties
{
    public string $publicProperty = 'public';
    protected string $protectedProperty = 'protected';
    private string $privateProperty = 'private';
    public static string $staticProperty = 'static';
}
