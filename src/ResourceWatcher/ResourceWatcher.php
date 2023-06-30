<?php

namespace T73Biz\PhpUnitWatcher\ResourceWatcher;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo as FinderFileInfo;

class ResourceWatcher
{
    private bool $isEnabledRelativePath = false;
    private ResourceCacheInterface $cache;
    private Finder $finder;
    private HashInterface $hasher;
    private array $fileHashesFromFinder = [];
    private array $newFiles = [];
    private array $deletedFiles = [];
    private array $updatedFiles = [];

    /**
     * Constructor.
     *
     * @param ResourceCacheInterface $resourceCache The cache.
     * @param Finder $finder The Symfony Finder.
     * @param HashInterface $hasher The file hash strategy.
     */
    public function __construct(ResourceCacheInterface $resourceCache, Finder $finder, HashInterface $hasher)
    {
        $this->cache = $resourceCache;
        $this->finder = $finder;
        $this->hasher = $hasher;
    }

    /**
     * Initializes the resource watcher.
     *
     * @return void
     */
    public function initialize(): void
    {
        if (!$this->cache->isInitialized()) {
            $this->findChanges();
        }
    }

    /**
     * Uses relative path with the resource cache.
     *
     * @return void
     */
    public function enableRelativePathWithCache(): void
    {
        $this->isEnabledRelativePath = true;
    }

    /**
     * Finds all the changes in the filesystem according to the finder criteria.
     *
     * @return ResourceWatcherResult
     */
    public function findChanges(): ResourceWatcherResult
    {
        $this->reset();

        if (!$this->cache->isInitialized()) {
            $this->warmUpCache();
        } else {
            $this->findChangesAgainstCache();
        }

        $this->cache->save();

        return new ResourceWatcherResult($this->newFiles, $this->deletedFiles, $this->updatedFiles);
    }

    /**
     * Rebuilds the resource cache
     *
     * @return void
     */
    public function rebuild(): void
    {
        $this->cache->erase();
        $this->reset();
        $this->warmUpCache();
        $this->cache->save();
    }

    /**
     * @return void
     */
    private function reset(): void
    {
        $this->newFiles = [];
        $this->deletedFiles = [];
        $this->updatedFiles = [];
    }

    /**
     * @return void
     */
    private function warmUpCache(): void
    {
        foreach ($this->finder as $file) {
            $filePath = $file->getPathname();
            $filePathForCache = $this->getFilePathForCache($file);
            $this->cache->write($filePathForCache, $this->calculateHashOfFile($filePath));
        }
    }

    /**
     * @return void
     */
    private function findChangesAgainstCache(): void
    {
        $this->calculateHashOfFilesFromFinder();

        $finderFileHashes = $this->fileHashesFromFinder;
        $cacheFileHashes = $this->cache->getAll();

        if (count($finderFileHashes) > count($cacheFileHashes)) {
            foreach ($finderFileHashes as $file => $hash) {
                $this->processFileFromFilesystem($file, $hash);
            }
        } else {
            foreach ($cacheFileHashes as $file => $hash) {
                $this->processFileFromCache($file, $hash);
            }
        }
    }

    /**
     * @param string $file
     * @param string $hash
     *
     * @return void
     */
    private function processFileFromFilesystem($file, $hash): void
    {
        $hashFromCache = $this->cache->read($file);

        if ($hashFromCache) {
            if ($hash != $hashFromCache) {
                $this->cache->write($file, $hash);
                $this->updatedFiles[] = $file;
            }
        } else {
            $this->cache->write($file, $hash);
            $this->newFiles[] = $file;
        }
    }

    /**
     * @return void
     */
    private function processFileFromCache($file, $hash): void
    {
        $hashFromCache = $this->fileHashesFromFinder[$file] ?? null;

        if ($hashFromCache) {
            if ($hashFromCache != $hash) {
                $this->cache->write($file, $hashFromCache);
                $this->updatedFiles[] = $file;
            }
        } else {
            $this->cache->delete($file);
            $this->deletedFiles[] = $file;
        }
    }

    /**
     * @return void
     */
    private function calculateHashOfFilesFromFinder(): void
    {
        $pathsAndHashes = [];

        foreach ($this->finder as $file) {
            $filePath = $file->getPathname();
            $filePathForCache = $this->getFilePathForCache($file);
            $pathsAndHashes[$filePathForCache] = $this->calculateHashOfFile($filePath);
        }

        $this->fileHashesFromFinder = $pathsAndHashes;
    }

    /**
     * @param $filename
     * @return string
     */
    private function calculateHashOfFile($filename): string
    {
        return $this->hasher->hash($filename);
    }

    /**
     * @param FinderFileInfo $file
     * @return string
     */
    private function getFilePathForCache(FinderFileInfo $file): string
    {
        if ($this->isEnabledRelativePath === true) {
            return $file->getRelativePathname();
        }

        return $file->getPathname();
    }
}
