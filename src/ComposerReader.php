<?php

declare(strict_types=1);

namespace PoolsPhp\Pools;

class ComposerReader
{
    public function getComposerJson(): array
    {
        return json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
    }
}
