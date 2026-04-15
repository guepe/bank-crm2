<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class DocumentStorage
{
    public function __construct(
        private readonly string $documentUploadDir,
        private readonly SluggerInterface $slugger
    ) {
    }

    public function store(UploadedFile $file): array
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir($this->documentUploadDir);

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = $this->slugger->slug($originalName)->lower();
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $filename = sprintf('%s-%s.%s', $safeName ?: 'document', bin2hex(random_bytes(6)), $extension);

        $size = $file->getSize();
        $file->move($this->documentUploadDir, $filename);

        return [
            'path' => $filename,
            'mime_type' => $file->getClientMimeType(),
            'size' => $size,
        ];
    }

    public function getAbsolutePath(string $relativePath): string
    {
        return rtrim($this->documentUploadDir, '/').'/'.$relativePath;
    }
}
