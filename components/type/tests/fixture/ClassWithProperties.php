<?php

declare(strict_types=1);

namespace Psl\Type\Tests\Fixture;

/**
 * @mago-expect lint:no-protected-in-final
 */
final class ClassWithProperties
{
    public string $publicProperty = 'public';
    protected string $protectedProperty = 'protected';
    private string $privateProperty = 'private';
    public static string $staticProperty = 'static';
}
