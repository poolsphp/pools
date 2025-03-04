<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use FilesystemIterator;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase as BaseTestCase;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class TestCase extends BaseTestCase
{
    protected string $fixturesPath = __DIR__.'/fixtures';

    protected ArrayInput $input;

    protected BufferedOutput $output;

    protected string $originalWorkingDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureFileSystem();
    }

    protected function tearDown(): void
    {
        chdir($this->originalWorkingDirectory);
        if (PHP_OS_FAMILY === 'Windows') {
            gc_collect_cycles();
            usleep(100000); // 100ms
        }

        $fs = new Filesystem();

        $maxAttempts = 3;
        $attempt = 1;
        $tempDir = $this->outputDirectory('temp');

        while ($attempt <= $maxAttempts) {
            try {
                if ($fs->exists($tempDir)) {
                    if (PHP_OS_FAMILY === 'Windows') {
                        $this->makeFilesWritable($tempDir);
                    }

                    $fs->deleteDirectory($tempDir);
                }
                break;
            } catch (Exception $e) {
                if ($attempt === $maxAttempts) {
                    error_log("Warning: Could not delete temporary directory: {$e->getMessage()}");
                } else {
                    usleep(500000 * $attempt); // Increasing delay: 500ms, 1s, etc.
                    $attempt++;
                }
            }
        }

        parent::tearDown();
    }

    protected function makeFilesWritable(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isWritable() === false) {
                @chmod($item->getPathname(), 0777);
            }
        }
    }

    protected function fixturePath(string $path): string
    {
        return $this->normalizePath($this->fixturesPath.'/'.$path);
    }

    protected function outputDirectory(?string $path = null): string
    {
        $base = $this->normalizePath($this->originalWorkingDirectory.'/test-output');

        if ($path === null) {
            return $this->normalizePath($base);
        }

        return $this->normalizePath($base.'/'.$path);
    }

    private function configureFileSystem(): void
    {
        $this->originalWorkingDirectory = getcwd();
        $fs = new Filesystem();

        $outputDir = $this->outputDirectory();
        if (!$fs->isDirectory($outputDir)) {
            $fs->makeDirectory($outputDir, 0777, true);
        }

        $tempDir = $this->outputDirectory('temp');
        if (!$fs->isDirectory($tempDir)) {
            $fs->makeDirectory($tempDir, 0777, true);
        }

        $composerJsonPath = $tempDir.DIRECTORY_SEPARATOR.'composer.json';
        if (!$fs->exists($composerJsonPath)) {
            $fs->copy(
                $this->fixturePath('composer.json'),
                $composerJsonPath
            );
        }

        chdir($tempDir);
    }

    protected function normalizePath(string $path): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $path = str_replace('/', '\\', $path);
            $path = str_replace('\\\\', '\\', $path);
        } else {
            $path = str_replace('\\', '/', $path);
            $path = str_replace('//', '/', $path);
        }

        return $path;
    }
}
