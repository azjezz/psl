<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\File;

use PHPUnit\Framework\TestCase;
use Psl\File;
use Psl\Filesystem;
use Psl\IO\Exception\AlreadyClosedException;

final class LockTest extends TestCase
{
    public function testRelease(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);

        $lock = $handle->lock(File\LockType::EXCLUSIVE);

        static::assertSame(File\LockType::EXCLUSIVE, $lock->type);

        $lock->release();

        $lock = $handle->tryLock(File\LockType::SHARED);

        static::assertSame(File\LockType::SHARED, $lock->type);

        $lock->release();
    }

    public function testLockingClosedFile(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);
        $handle->close();

        $this->expectException(AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $handle->lock(File\LockType::EXCLUSIVE);
    }

    public function testReleasingALockOnAClosedFile(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);

        $lock = $handle->lock(File\LockType::EXCLUSIVE);

        static::assertSame(File\LockType::EXCLUSIVE, $lock->type);

        $handle->close();

        $this->expectException(AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle was closed before releasing the lock.');

        $lock->release();
    }

    public function testReleasingALockOnAClosedFileUsingDestructor(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);

        $lock = $handle->lock(File\LockType::EXCLUSIVE);

        static::assertSame(File\LockType::EXCLUSIVE, $lock->type);

        $handle->close();

        $this->expectException(AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle was closed before releasing the lock.');

        unset($lock);
    }

    public function testReleasingASecondTimeAfterClosingTheFile(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);

        $lock = $handle->lock(File\LockType::EXCLUSIVE);

        static::assertSame(File\LockType::EXCLUSIVE, $lock->type);

        $lock->release();

        $lock = $handle->tryLock(File\LockType::SHARED);

        static::assertSame(File\LockType::SHARED, $lock->type);

        $lock->release();
        $handle->close();

        // this should not throw.
        $lock->release();
    }
}
