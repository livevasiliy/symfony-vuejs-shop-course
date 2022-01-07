<?php


namespace App\Services\Filesystem;


use Symfony\Component\Filesystem\Filesystem;

class FilesystemWorker
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem) {
        $this->filesystem = $filesystem;
    }

    public function createFolderIfNotExist(string $folder): void
    {
        if (!$this->filesystem->exists($folder)) {
            $this->filesystem->mkdir($folder);
        }
    }

    public function remove(string $item): void
    {
        if ($this->filesystem->exists($item)) {
            $this->filesystem->remove($item);
        }
    }
}