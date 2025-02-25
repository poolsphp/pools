<?php

declare(strict_types=1);

namespace PoolsPhp\Pools;

use function file_get_contents;
use function is_string;

class ComposerReader
{
    public function getComposerJson(): mixed
    {
        $fileContents = file_get_contents(__DIR__.'/../composer.json');
        assert(is_string($fileContents));

        return json_decode($fileContents, true);
    }
}
