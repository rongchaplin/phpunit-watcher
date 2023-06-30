<?php

namespace T73Biz\PhpUnitWatcher\ResourceWatcher;

class ResourceCacheMemory implements ResourceCacheInterface
{
    protected bool $isInitialized = false;
    private array $data = [];

    /**
     * {@inheritdoc}
     */
    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    /**
     * {@inheritdoc}
     */
    public function read($filename): string
    {
        return $this->data[$filename] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($filename, $hash): void
    {
        $this->data[$filename] = $hash;
        $this->isInitialized = true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($filename): void
    {
        unset($this->data[$filename]);
    }

    /**
     * {@inheritdoc}
     */
    public function erase(): void
    {
        $this->data = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function save(): void
    {
        $this->isInitialized = true;
    }
}
