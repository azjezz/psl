<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Type;

use Psl;
use Psl\Type;

/**
 * @param class-string<Psl\Collection\CollectionInterface> $_foo
 */
function take_collection_classname(string $_foo): void
{
}

/**
 * @throws Psl\Type\Exception\AssertException
 */
function tests(): void
{
    take_collection_classname(Type\class_string(Psl\Collection\CollectionInterface::class)->assert('foo'));
}
