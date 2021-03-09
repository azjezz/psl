<?php

declare(strict_types=1);

namespace Psl\Math;

use const INF;
use const NAN as PHP_NAN;

const INT64_MAX = 9223372036854775807;
const INT64_MIN = -1 << 63;

const INT53_MAX = 9007199254740992;
const INT53_MIN = -9007199254740993;

const INT32_MAX = 2147483647;
const INT32_MIN = -2147483648;

const INT16_MAX = 32767;
const INT16_MIN = -32768;

const UINT32_MAX = 4294967295;
const UINT16_MAX = 65535;

const PI = 3.141592653589793238462643;

/**
 * The base of the natural system of logarithms, or approximately 2.7182818284590452353602875.
 */
const E = 2.7182818284590452353602875;

/**
 * The infinite.
 *
 * @var int
 */
const INFINITY = INF;

/**
 * Not A Number.
 *
 * @var float
 */
const NAN = PHP_NAN;
