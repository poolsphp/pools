<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase as BaseTestCase;
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
        $fs = new Filesystem();
        $fs->deleteDirectory(
            $this->outputDirectory('temp')
        );

        // We should remove the vendor directory
        parent::tearDown();
    }

    protected function fixturePath(string $path): string
    {
        return $this->fixturesPath.'/'.$path;
    }

    protected function outputDirectory(?string $path = null): string
    {
        return $this->originalWorkingDirectory.'/test-output/'.$path;
    }

    private function configureFileSystem(): void
    {
        $this->originalWorkingDirectory = getcwd();
        $fs = new Filesystem();
        if (! $fs->exists($this->outputDirectory('temp/composer.json'))) {
            if (! $fs->isDirectory($this->outputDirectory('temp'))) {
                $fs->makeDirectory($this->outputDirectory('temp'));
            }
            $fs->copy(
                $this->fixturePath('composer.json'),
                $this->outputDirectory('temp/composer.json')
            );
        }

        chdir($this->outputDirectory('temp'));
    }
}
