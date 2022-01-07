<?php

namespace App\Services\File;

use App\Services\Filesystem\FilesystemWorker;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileSaver
{
    private FilesystemWorker $filesystemWorker;
    private string $uploadsTempDir;
    private SluggerInterface $slugger;

    public function __construct(
        SluggerInterface $slugger,
        string $uploadsTempDir,
        FilesystemWorker $filesystemWorker
    ) {
        $this->slugger = $slugger;
        $this->uploadsTempDir = $uploadsTempDir;
        $this->filesystemWorker = $filesystemWorker;
    }

    public function saveUploadedFileIntoTemp(UploadedFile $uploadedFile): ?string
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $filename = sprintf('%s-%s.%s', $safeFilename, uniqid(), $uploadedFile->guessExtension());

        $this->filesystemWorker->createFolderIfNotExist($this->uploadsTempDir);

        try {
            $uploadedFile->move($this->uploadsTempDir, $filename);
        } catch (FileException $exception) {
            // TODO: Add logger error's
            return null;
        }

        return $filename;
    }
}
