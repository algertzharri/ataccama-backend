<?php

namespace App\Services;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * @param UploadedFile $file
     * @param string $fileName
     * @param string $fileType
     * @return string
     */
    public function base64FileUpload(UploadedFile $file, string $fileName, string $fileType): string
    {
        if ($file->getSize() <= 5000000 && $this->validateExtension($file, $fileType)) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            return $this->moveFile($file, $fileName, $extension);
        }
        throw new Exception("File type or size not supported!");
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    public function fileUpload(UploadedFile $file): string
    {
        if ($file->getSize() <= 5000000 && $this->validateExtension($file)) {
            $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            return $this->moveFile($file, $originalFileName, $file->getClientOriginalExtension());
        }
        throw new Exception("File type or size not supported!");
    }

    /**
     * @param UploadedFile $file
     * @param string $fileName
     * @param string $extension
     * @return string
     */
    public function moveFile(UploadedFile $file, string $fileName, string $extension): string
    {
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-ZA-z0-9_] remove; Lower()', $fileName);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $extension;

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            throw new Exception("Error uploading file!");
        }
        return $fileName;
    }

    /**
     * @param UploadedFile $file
     * @param string $fileType
     * @return bool
     */
    private function validateExtension(UploadedFile $file, string $fileType = ""): bool
    {
        $allowed = array(
            'image/jpeg',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );

        if (in_array($file->getClientMimeType(), $allowed) || in_array($fileType, $allowed))
        {
            return true;
        }

        return false;
    }
}
