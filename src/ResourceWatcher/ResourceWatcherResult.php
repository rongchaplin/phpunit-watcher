<?php

namespace T73Biz\PhpUnitWatcher\ResourceWatcher;

use Symfony\Component\Finder\Finder;

class ResourceWatcherResult
{
    private bool $hasChanges;
    private array $newResources;
    private array $deletedResources;
    private array $updatedResources;

    /**
     * Constructor.
     */
    public function __construct(array $newResources, array $deletedResources, array $updatedResources)
    {
        $this->deletedResources = $deletedResources;
        $this->hasChanges = false;
        $this->newResources = $newResources;
        $this->updatedResources = $updatedResources;
    }
    /**
     * Has any change in resources?
     *
     * @return bool
     */
    public function hasChanges(): bool
    {
        if ($this->hasChanges) {
            return $this->hasChanges;
        }

        $this->hasChanges = count($this->newResources) > 0 || count($this->deletedResources) > 0 || count($this->updatedResources) > 0;

        return $this->hasChanges;
    }

    /**
     * Returns an array with paths of the new resources ('.', '..' not resolved).
     *
     * @return array
     */
    public function getNewFiles(): array
    {
        return $this->newResources;
    }

    /**
     * Returns an array with path of the deleted resources ('.', '..' not resolved).
     *
     * @return array
     */
    public function getDeletedFiles(): array
    {
        return $this->deletedResources;
    }

    /**
     * Returns an array with path of the updated resources ('.', '..' not resolved).
     *
     * @return array
     */
    public function getUpdatedFiles(): array
    {
        return $this->updatedResources;
    }
}
