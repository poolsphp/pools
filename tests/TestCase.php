<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PoolsPhp\Pools\ComposerManager;
use PoolsPhp\Pools\Exceptions\NoComposerException;
use PoolsPhp\Pools\PHPPackageInstaller;
use PoolsPhp\Pools\ValueObjects\PackagePayload;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;

use function unlink;

abstract class TestCase extends BaseTestCase
{
    protected ComposerManager $reader;

    protected string $fixturesPath = __DIR__.'/fixtures';

    protected ArrayInput $input;

    protected BufferedOutput $output;

    protected ComposerManager $composerManager;

    protected PHPPackageInstaller $phpPackageInstaller;

    protected PackagePayload $packagePayload;

    protected string $originalWorkingDirectory;

    /**
     * @throws NoComposerException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->configureFileSystem();
        $this->configureDependencies();
    }

    protected function tearDown(): void
    {

        if (file_exists($this->getTempFileSystemPath('composer.json'))) {

            unlink($this->getTempFileSystemPath('composer.json'));
        }

        if (file_exists($this->getTempFileSystemPath('composer.lock'))) {
            unlink($this->getTempFileSystemPath('composer.lock'));
        }
        // We should remove the vendor directory
        parent::tearDown();
    }

    protected function getFixturePath(string $path): string
    {
        return $this->fixturesPath.'/'.$path;
    }

    protected function getTempFileSystemPath(?string $path = null): string
    {
        return $this->originalWorkingDirectory.'/.temp/filesystem/'.$path;
    }

    private function configureFileSystem(): void
    {
        $this->originalWorkingDirectory = getcwd();
        $fs = new Filesystem();
        if (! $fs->exists($this->getTempFileSystemPath('composer.json'))) {
            $fs->copy(
                $this->getFixturePath('composer.json'),
                $this->getTempFileSystemPath('composer.json')
            );
        }

        chdir($this->getTempFileSystemPath());
    }

    /**
     * @throws NoComposerException
     */
    private function configureDependencies(): void
    {
        $this->input = new ArrayInput([]);
        $this->output = new BufferedOutput();
        $this->composerManager = new ComposerManager(
            $this->getTempFileSystemPath('composer.json')
        );
        $this->phpPackageInstaller = new PHPPackageInstaller($this->composerManager);
        $this->packagePayload = new PackagePayload(
            $this->phpPackageInstaller,
            $this->composerManager,
            $this->input,
            $this->output
        );
    }
}
