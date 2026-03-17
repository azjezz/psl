<?php

declare(strict_types=1);

namespace Psl\Type\Tests\StaticAnalysis;

use Psl;
use Psl\Type;

/**
 * @param class-string<Psl\Collection\CollectionInterface> $_
 */
function take_collection_classname(string $_): void {}

/**
 * @throws Psl\Type\Exception\AssertException
 */
function tests(): void
{
    take_collection_classname(Type\class_string(Psl\Collection\CollectionInterface::class)->assert('foo'));
}
