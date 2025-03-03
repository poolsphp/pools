<?php

declare(strict_types=1);

namespace PoolsPhp\Pools;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Package\Link;
use Composer\PartialComposer;
use Pools\Exceptions\NoComposerException;

use function file_exists;
use function getcwd;

final class ComposerManager
{
    private Composer|PartialComposer $composer;

    /**
     * @throws NoComposerException
     */
    public function __construct(
        private ?string $composerPath = null,
    ) {
        if ($composerPath === null || $composerPath === '' || $composerPath === '0') {
            $this->composerPath = getcwd().'/composer.json';
        }
        $this->loadComposer($this->composerPath);
    }

    /**
     * Get the Composer instance
     *
     * @throws NoComposerException
     */
    public function getComposer(?string $composerPath = null): Composer|PartialComposer
    {
        if ($composerPath !== null) {
            $this->loadComposer($composerPath);
        }

        return $this->composer;
    }

    /**
     * Get dev dependencies from composer.json
     *
     * @return array<string, Link>
     *
     * @throws NoComposerException
     */
    public function getDevDependencies(?string $composerPath = null): array
    {
        if ($composerPath !== null) {
            $this->loadComposer($composerPath);
        }

        return $this->composer
            ->getPackage()
            ->getDevRequires();
    }

    /**
     * @throws NoComposerException
     */
    public function isPackageInstalled(string $packageName, ?string $composerPath = null): bool
    {
        $devDependencies = $this->getDevDependencies($composerPath);

        return isset($devDependencies[$packageName]);
    }

    /**
     * @throws NoComposerException
     */
    private function loadComposer(string $composerPath): void
    {
        $this->composerPath = $composerPath;

        if (! file_exists($this->composerPath)) {
            throw new NoComposerException("Composer file not found at: {$this->composerPath}");
        }

        $this->composer = (new Factory())
            ->createComposer(new NullIO(), $this->composerPath);
    }
}
