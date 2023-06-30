<?php

namespace T73Biz\PhpUnitWatcher\ResourceWatcher;

class Crc32ContentHash implements HashInterface
{
    /**
     * @inheritDoc
     */
    public function hash(string $filepath): string
    {
        $fileContent = $filepath;

        if (!\is_dir($filepath)) {
            $fileContent = file_get_contents($filepath);
        }

        return hash('crc32', $fileContent);
    }
}
