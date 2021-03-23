<?php

use Psl\Filesystem;
use Psl\Str;
use Psl\Asio;
use Psl\IO;

require_once __DIR__ . '/../vendor/autoload.php';

Filesystem\create_directory(__DIR__ . '/cache');

$start_time = Asio\time();
$handles = [];
for ($i = 0; $i < 1002; $i++) {
  $handles[] = Asio\async(
    /**
     * @throws IO\Exception\ExceptionInterface
     * @throws Asio\Exception\ExceptionInterface
     * @throws Filesystem\Exception\ExceptionInterface
     */
    static function () use ($i, $start_time): void {
      $file = Filesystem\open_file_read_write(__DIR__ . "/cache/example${i}.txt");
      $file->writeAll('Hello, World!');

      Psl\invariant($file->tell() === 13, 'invalid position.');

      $file->writeAll("\n");
      $file->writeAll('Hello, World!');

      Psl\invariant($file->tell() === 27, 'invalid position.');

      if ($i === 100) {
        Asio\await(Asio\sleep(50));
      }

      $file->seek(0);

      Psl\invariant($file->tell() === 0, 'invalid position.');

      $content = $file->readFixedSize(13);

      Psl\invariant($content === 'Hello, World!', 'invalid content.');

      $file->seek($file->tell() + 1);

      $content = $file->readFixedSize(13);

      Psl\invariant($content === 'Hello, World!', 'invalid content.');

      Psl\invariant(Str\Byte\ends_with($file->getPath(), "/cache/example${i}.txt"), 'Invalid path');

      $file->seek(314);
      $file->writeAll('x');
      $file->seek(300);

      $content = $file->readAll();

      Psl\invariant(Str\Byte\length($content) === 15, 'unexpected error.');

      Psl\invariant(Str\Byte\starts_with($content, Str\repeat("\0", 14)), 'content is not prefixed with NUL byte');

      Psl\invariant(Str\Byte\ends_with($content, 'x'), 'content does not contain x');

      $file->close();

      IO\output_handle()
        ->writeAll(
          Str\format("[%d] after %dms\n", $i + 1, Asio\time() - $start_time)
        );
    }
  );
}

Asio\await(Asio\all($handles));

IO\output_handle()->writeAll(Str\format("\nmodified 1000 files in %dms\n\n", Asio\time() - $start_time));

Filesystem\delete_directory(__DIR__ . '/cache', true);
