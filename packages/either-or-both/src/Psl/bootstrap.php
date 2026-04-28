<?php

declare(strict_types=1);

/**
 * The three free-function variant constructors (`left`, `right`, `both`) live in a
 * single `EitherOrBoth/functions.php` file rather than one file per function (as is
 * the convention elsewhere in Psl, e.g. `Option/some.php`). See the comment at the
 * top of that file for the reason — in short, the sibling class files `Left.php`,
 * `Right.php`, and `Both.php` would collide with lowercase-named function files on
 * case-insensitive filesystems.
 */

(static function (): void {
    if (function_exists('Psl\EitherOrBoth\left')) {
        return;
    }

    require_once __DIR__ . '/EitherOrBoth/functions.php';
})();
