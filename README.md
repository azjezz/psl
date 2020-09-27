# Psl - PHP Standard Library

[![TravisCI Build Status](https://travis-ci.com/azjezz/psl.svg?branch=develop)](https://travis-ci.com/azjezz/psl)
[![Scrutinizer Build Status](https://scrutinizer-ci.com/g/azjezz/psl/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/azjezz/psl/build-status/develop)
[![Coverage Status](https://coveralls.io/repos/github/azjezz/psl/badge.svg?branch=develop)](https://coveralls.io/github/azjezz/psl?branch=develop)
[![Type Coverage](https://shepherd.dev/github/azjezz/psl/coverage.svg)](https://shepherd.dev/github/azjezz/psl)
[![SymfonyInsight](https://insight.symfony.com/projects/1e053a4a-aaab-4a52-9059-c4883bfd46f7/mini.svg)](https://insight.symfony.com/projects/1e053a4a-aaab-4a52-9059-c4883bfd46f7)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/azjezz/psl/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/azjezz/psl/?branch=develop)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/azjezz/psl/badges/code-intelligence.svg?b=develop)](https://scrutinizer-ci.com/code-intelligence)
[![Total Downloads](https://poser.pugx.org/azjezz/psl/d/total.svg)](https://packagist.org/packages/azjezz/psl)
[![Latest Stable Version](https://poser.pugx.org/azjezz/psl/v/stable.svg)](https://packagist.org/packages/azjezz/psl)
[![License](https://poser.pugx.org/azjezz/psl/license.svg)](https://packagist.org/packages/azjezz/psl)

Psl is a standard library for PHP, inspired by [hhvm/hsl](https://github.com/hhvm/hsl).

The goal of Psl is to provide a consistent, centralized, well-typed set of APIs for PHP programmers.

## Example
```php
<?php

declare(strict_types=1);

use Psl\Arr;
use Psl\Str;

/**
 * @psalm-param iterable<?int> $codes
 */
function foo(iterable $codes): string
{
    /** @var list<int> $codes */
    $codes = Arr\filter_nulls($codes);
    /** @var list<string> $chars */
    $chars = Arr\map($codes, fn(int $code): string => Str\chr($code));

    return Str\join($chars, ', ');
}

foo([95, 96, null, 98]);
// 'a, b, d'
```

## Installation

This package doesn't have a stable release yet, but you can still install it using composer :

```console
$ composer require azjezz/psl:dev-develop
```

## Documentation

Documentation is not available yet.

## Principles

 - All functions should be typed as strictly as possible
 - The library should be internally consistent
 - References may not be used
 - Arguments should be as general as possible. For example, for `array` functions, prefer `iterable` inputs where practical, falling back to `array` when needed.
 - Return types should be as specific as possible
 - All files should contain `declare(strict_types=1);`

## Consistency Rules

This is not exhaustive list.

 - Functions argument order should be consistent within the library
   - All iterable-related functions take the iterable as the first argument ( e.g. `Iter\map` and `Iter\filter` )
   - `$haystack`, `$needle`, and `$pattern` are in the same order for all functions that take them
 - Functions should be consistently named.
 - If an operation can conceivably operate on either keys or values, the default is to operate on the values - the version that operates on keys should have `_key` suffix (e.g. `Iter\last`, `Iter\last_key`, `Iter\contains`, `Iter\contains_key` )
 - Find-like operations that can fail should return `?T`; a second function should be added with an `x` suffix that uses an invariant to return `T` (e.g. `Arr\last`, `Arr\lastx`)
 - Iterable functions that do an operation based on a user-supplied keying function for each element should be suffixed with `_by` (e.g. `Arr\sort_by`, `Iter\group_by`, `Math\max_by`)

## Sponsors

Thanks to our sponsors and supporters:


| JetBrains |
|---|
| <a href="https://www.jetbrains.com/?from=PSL ( PHP Standard Library )" title="JetBrains" target="_blank"><img src="https://res.cloudinary.com/azjezz/image/upload/v1599239910/jetbrains_qnyb0o.png" height="120" /></a> |


## License

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information.
