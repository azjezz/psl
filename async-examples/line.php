<?php

use Psl\Asio;
use Psl\Str;
use Psl\IO;

require_once __DIR__ . '/../vendor/autoload.php';

$input = IO\input_handle();
$output = IO\output_handle();

$reader = new IO\Reader($input);

$output->writeAll('username: ');
$username = $reader->readLine();

$output->writeAll('password: ');
$password = $reader->readLine();

$output->writeAll(Str\format('- username: %s%s', $username, "\n"));
$output->writeAll(Str\format('- password: %s%s', $password, "\n"));
