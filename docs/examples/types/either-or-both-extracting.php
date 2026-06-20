<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\EitherOrBoth;

$both = new EitherOrBoth\Both::<string, string>('hello', 'world');

// Direct access -- throws if the side is missing
$both->getLeft(); // 'hello'
$both->getRight(); // 'world'

$left = new EitherOrBoth\Left::<string>('only-left');
$left->getLeft(); // 'only-left'
// $left->getRight();            // throws MissingRightException

// Safe access via Option
$left->unwrapLeft(); // Some('only-left')
$left->unwrapRight(); // None

// Option methods cover the "or default" variations without duplicate surface on EitherOrBoth:
$left->unwrapRight()->unwrapOr::<string>('fallback'); // 'fallback'
$left->unwrapLeft()->unwrapOrElse::<string>(static fn(): string => 'computed'); // 'only-left'
