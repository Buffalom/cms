<?php

namespace Tests\Composer;

use Statamic\Console\Composer\Lock;

/**
 * Test that we can backup a composer lock file using vanilla PHP so that it can be run in a Composer hook.
 */
class ComposerLockBackupTest extends \PHPUnit\Framework\TestCase
{
    protected $lockPath = './composer.lock';
    protected $customLockPath = './custom/composer.lock';
    protected $backupLockPath = './storage/statamic/updater/composer.lock.bak';
    protected $customBackupLockPath = './custom/storage/statamic/updater/composer.lock.bak';
    protected $mockLockPath = './storage/statamic/updater/composer.lock.mock';
    protected $customMockLockPath = './custom/storage/statamic/updater/composer.lock.mock';

    public function setUp(): void
    {
        parent::setUp();

        $this->removeLockFiles();
    }

    public function tearDown(): void
    {
        $this->removeLockFiles();

        parent::tearDown();
    }

    /** @test */
    public function it_can_backup_existing_lock_file()
    {
        file_put_contents($this->lockPath, $content = 'test lock file content');

        $this->assertFileExists($this->lockPath);
        $this->assertFileNotExists($this->backupLockPath);

        Lock::backup();

        $this->assertFileExists($this->backupLockPath);
        $this->assertEquals($content, file_get_contents($this->backupLockPath));
    }

    /** @test */
    public function it_can_mock_existing_lock_file()
    {
        file_put_contents($this->lockPath, $content = 'test lock file content');

        $this->assertFileExists($this->lockPath);
        $this->assertFileNotExists($this->mockLockPath);

        Lock::mock();

        $this->assertFileExists($this->mockLockPath);
        $this->assertEquals($content, file_get_contents($this->mockLockPath));
    }

    /** @test */
    public function it_doesnt_throw_exception_when_attempting_to_backup_non_existend_lock_file()
    {
        Lock::backup('non-existent-file.lock');

        $this->assertFileNotExists($this->backupLockPath);
    }

    /** @test */
    public function it_can_backup_lock_file_from_custom_location()
    {
        if (! is_dir($dir = './custom')) {
            mkdir($dir);
        }

        file_put_contents($this->customLockPath, $content = 'custom lock file content');

        $this->assertFileExists($this->customLockPath);
        $this->assertFileNotExists($this->customBackupLockPath);

        Lock::backup($this->customLockPath);

        $this->assertFileExists($this->customBackupLockPath);
        $this->assertEquals($content, file_get_contents($this->customBackupLockPath));
    }

    /** @test */
    public function it_can_mock_lock_file_from_custom_location()
    {
        if (! is_dir($dir = './custom')) {
            mkdir($dir);
        }

        file_put_contents($this->customLockPath, $content = 'custom lock file content');

        $this->assertFileExists($this->customLockPath);
        $this->assertFileNotExists($this->customMockLockPath);

        Lock::mock($this->customLockPath);

        $this->assertFileExists($this->customMockLockPath);
        $this->assertEquals($content, file_get_contents($this->customMockLockPath));
    }

    private function removeLockFiles()
    {
        $files = [
            $this->lockPath,
            $this->customLockPath,
            $this->backupLockPath,
            $this->customBackupLockPath,
            $this->mockLockPath,
            $this->customMockLockPath,
        ];

        foreach ($files as $lockFile) {
            if (is_file($lockFile)) {
                unlink($lockFile);
            }
        }
    }

    /**
     * @deprecated
     */
    public static function assertFileNotExists(string $filename, string $message = ''): void
    {
        method_exists(static::class, 'assertFileDoesNotExist')
            ? static::assertFileDoesNotExist($filename, $message)
            : parent::assertFileNotExists($filename, $message);
    }
}
