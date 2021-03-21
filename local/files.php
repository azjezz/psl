<?php

use Psl\Asio;
use Psl\Str;
use Psl\IO;
use Psl\Filesystem;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Create and write to 100 files ( using Asio ).
 */
function write_async(): void
{
  $awaitables = [];
  for ($i = 0; $i < 100; $i++) {
    $awaitables[] = Asio\async(function () use ($i) {
      $handle = IO\Internal\open(__DIR__ . '/tmp1/' . $i . '.txt', 'x');
      $handle->writeAll(Str\repeat("hello", 100000));
    });
  }

  Asio\await(Asio\all($awaitables));
}
/**
 * Create and write to 100 files ( using IO from version 1.5 ).
 */
function write_sync(): void
{
  for ($i = 0; $i < 100; $i++) {
    $handle = IO\Internal\open(__DIR__ . '/tmp2/' . $i . '.txt', 'x');
    $bytes = Str\repeat("hello", 100000);
    $written = 0;
    do {
      $written += $handle->writeImmediately($bytes);
    } while ($written !== Str\length($bytes));
  }
}
function main(): void
{
  Filesystem\delete_directory(__DIR__ . '/tmp1', true); Filesystem\create_directory(__DIR__ . '/tmp1');
  Filesystem\delete_directory(__DIR__ . '/tmp2', true); Filesystem\create_directory(__DIR__ . '/tmp2');
  $start = Asio\time();
  write_sync();
  $total = Asio\time() - $start;
  echo "\n\n------\n\n sync: ${total}ms \n\n-----------\n\n";
  $start = Asio\time();
  write_async();
  $total = Asio\time() - $start;
  echo "\n\n------\n\n async: ${total}ms \n\n-----------\n\n";
}

main();
