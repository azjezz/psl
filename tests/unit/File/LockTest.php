<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\File;

use PHPUnit\Framework\TestCase;
use Psl\File;
use Psl\IO\Exception\AlreadyClosedException;

final class LockTest extends TestCase
{
    public function testRelease(): void
    {
        $file = File\temporary();

        $lock = $file->lock(File\LockType::EXCLUSIVE);

        static::assertSame(File\LockType::EXCLUSIVE, $lock->type);

        $lock->release();

        $lock = $file->tryLock(File\LockType::SHARED);

        static::assertSame(File\LockType::SHARED, $lock->type);

        $lock->release();
    }

    public function testLockingClosedFile(): void
    {
        $file = File\temporary();
        $file->close();

        $this->expectException(AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle has already been closed.');

        $file->lock(File\LockType::EXCLUSIVE);
    }

    public function testReleasingALockOnAClosedFile(): void
    {
        $file = File\temporary();

        $lock = $file->lock(File\LockType::EXCLUSIVE);

        static::assertSame(File\LockType::EXCLUSIVE, $lock->type);

        $file->close();

        $this->expectException(AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle was closed before releasing the lock.');

        $lock->release();
    }

    public function testReleasingALockOnAClosedFileUsingDestructor(): void
    {
        $file = File\temporary();

        $lock = $file->lock(File\LockType::EXCLUSIVE);

        static::assertSame(File\LockType::EXCLUSIVE, $lock->type);

        $file->close();

        $this->expectException(AlreadyClosedException::class);
        $this->expectExceptionMessage('Handle was closed before releasing the lock.');

        unset($lock);
    }

    public function testReleasingASecondTimeAfterClosingTheFile(): void
    {
        $file = File\temporary();

        $lock = $file->lock(File\LockType::EXCLUSIVE);

        static::assertSame(File\LockType::EXCLUSIVE, $lock->type);

        $lock->release();

        $lock = $file->tryLock(File\LockType::SHARED);

        static::assertSame(File\LockType::SHARED, $lock->type);

        $lock->release();
        $file->close();

        // this should not throw.
        $lock->release();
    }
}
