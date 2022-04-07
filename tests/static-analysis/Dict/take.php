<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Dict;

use Psl\Dict;

/** @param list<string> $_foo */
function take_string_list(array $_foo): void
{
}

/** @param array<string, string> $_foo */
function take_string_dict(array $_foo): void
{
}

function test(): void
{
    take_string_list(
        Dict\take(['a', 'b', 'c'], 2)
    );

    take_string_dict(
        Dict\take(['a' => 'c', 'b' => 'd'], 2)
    );
}
