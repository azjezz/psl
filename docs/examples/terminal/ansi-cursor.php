<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ansi\Cursor;
use Psl\IO;

IO\write(Cursor\move_to(1, 1)->toString()); // absolute position (1-based row, column)
IO\write(Cursor\up(5)->toString()); // relative movement
IO\write(Cursor\down(3)->toString());
IO\write(Cursor\forward(10)->toString());
IO\write(Cursor\back(2)->toString());
IO\write(Cursor\save()->toString()); // save position
IO\write(Cursor\restore()->toString()); // restore saved position
IO\write(Cursor\hide()->toString()); // hide cursor
IO\write(Cursor\show()->toString()); // show cursor
