<?php

declare(strict_types=1);

namespace Psl\File\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\File;
use Psl\Filesystem;
use Psl\IO\Exception\AlreadyClosedException;

final class LockTest extends TestCase
{
    public function testRelease(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);

        $lock = $handle->lock(File\LockType::Exclusive);

        static::assertSame(File\LockType::Exclusive, $lock->type);

        $lock->release();

        $lock = $handle->tryLock(File\LockType::Shared);

        static::assertSame(File\LockType::Shared, $lock->type);

        $lock->release();
    }

    public function testLockingClosedFile(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);
        $handle->close();

        $this->expectException(AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $handle->lock(File\LockType::Exclusive);
    }

    public function testReleasingALockOnAClosedFile(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);

        $lock = $handle->lock(File\LockType::Exclusive);

        static::assertSame(File\LockType::Exclusive, $lock->type);

        $handle->close();

        $this->expectException(AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle was closed before releasing the lock.');

        $lock->release();
    }

    public function testReleasingALockOnAClosedFileUsingDestructor(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);

        $lock = $handle->lock(File\LockType::Exclusive);

        static::assertSame(File\LockType::Exclusive, $lock->type);

        $handle->close();

        $this->expectException(AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle was closed before releasing the lock.');

        unset($lock);
    }

    public function testReleasingASecondTimeAfterClosingTheFile(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);

        $lock = $handle->lock(File\LockType::Exclusive);

        static::assertSame(File\LockType::Exclusive, $lock->type);

        $lock->release();

        $lock = $handle->tryLock(File\LockType::Shared);

        static::assertSame(File\LockType::Shared, $lock->type);

        $lock->release();
        $handle->close();

        // this should not throw.
        $lock->release();
    }

    public function testLockWithCancellationToken(): void
    {
        $file = Filesystem\create_temporary_file();
        $handle = File\open_read_write($file);

        $lock = $handle->lock(File\LockType::Shared, new Async\NullCancellationToken());

        static::assertSame(File\LockType::Shared, $lock->type);

        $lock->release();
        $handle->close();
    }

    public function testLockCancelledByTimeout(): void
    {
        $file = Filesystem\create_temporary_file();

        $handle1 = File\open_read_write($file);
        $lock1 = $handle1->lock(File\LockType::Exclusive);

        $handle2 = File\open_read_write($file);

        try {
            $handle2->lock(File\LockType::Exclusive, new Async\TimeoutCancellationToken(Duration::milliseconds(50)));

            static::fail('Exception should have been thrown.');
        } catch (Async\Exception\CancelledException) {
            static::addToAssertionCount(1);
        } finally {
            $lock1->release();
            $handle1->close();
            $handle2->close();
        }
    }
}
