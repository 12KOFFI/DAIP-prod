<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class MediaService
{
    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly FileUploader $fileUploader,
        private readonly SluggerInterface $slugger,
    ) {}

    /**
     * Generic image upload for a given context (e.g., 'recrutement', 'vae', 'formation').
     * Stores into upload_directory/[context]/ and returns the stored filename.
     */
    public function uploadImage(UploadedFile $file, string $context, string $type): string
    {
        $baseDir = rtrim($this->params->get('upload_directory'), DIRECTORY_SEPARATOR);
        $targetDir = $baseDir . DIRECTORY_SEPARATOR . trim($context, DIRECTORY_SEPARATOR);

        $this->ensureDirectory($targetDir);

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $extension = $file->guessExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $newFilename = $type . '_' . $safeFilename . '-' . uniqid() . '.' . $extension;

        $file->move($targetDir, $newFilename);

        return $newFilename;
    }

    /**
     * Upload a file for the recrutement context (image, logo, banniere) into upload_directory.
     * Returns the stored filename.
     */
    public function uploadRecruitment(UploadedFile $file, string $type): string
    {
        $baseDir = rtrim($this->params->get('upload_directory'), DIRECTORY_SEPARATOR);
        
        // Définir le sous-dossier en fonction du type de fichier
        $subfolder = match($type) {
            'logo' => 'logos',
            'banniere' => 'bannieres',
            'image' => 'images',
            'image_annonce' => 'image_annonce',
            default => 'documents',
        };
        
        $targetDir = $baseDir . DIRECTORY_SEPARATOR . 'recrutement' . DIRECTORY_SEPARATOR . $subfolder;
        $this->ensureDirectory($targetDir);

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $extension = $file->guessExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $newFilename = $type . '_' . $safeFilename . '-' . uniqid() . '.' . $extension;

        $file->move($targetDir, $newFilename);

        return 'recrutement/' . $subfolder . '/' . $newFilename;
    }

    /**
     * Delete a file stored under upload_directory/[context]/ by filename.
     */
    public function deleteImage(string $context, string $filename): bool
    {
        $path = rtrim($this->params->get('upload_directory'), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . trim($context, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . $filename;
        if (is_file($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * Delete a recrutement file from the appropriate subdirectory.
     */
    public function deleteRecruitment(string $filepath): bool
    {
        $basePath = rtrim($this->params->get('upload_directory'), DIRECTORY_SEPARATOR);
        $fullPath = $basePath . DIRECTORY_SEPARATOR . $filepath;
        
        if (is_file($fullPath)) {
            $success = unlink($fullPath);
            
            // Optionally: remove the parent directory if it's empty
            $dir = dirname($fullPath);
            if (is_dir($dir) && count(scandir($dir)) == 2) { // 2 because of . and ..
                rmdir($dir);
            }
            
            return $success;
        }
        return false;
    }

    /**
     * Proxy to existing FileUploader for candidature flows when needed.
     */
    public function uploadCandidature(UploadedFile $file, string $numCandidature, string $type): array
    {
        return $this->fileUploader->upload($file, $numCandidature, $type);
    }

    private function ensureDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException('Impossible de créer le répertoire d\'upload: ' . $dir);
            }
        }
        if (!is_writable($dir)) {
            throw new \RuntimeException('Le répertoire d\'upload n\'est pas accessible en écriture: ' . $dir);
        }
    }
}
