<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileUploader
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function upload(UploadedFile $file, string $numCandidature, string $type): array
    {
        // Vérifier que le fichier est valide
        if (!$file->isValid()) {
            throw new \RuntimeException('Fichier invalide. Code d\'erreur: ' . $file->getError());
        }

        // Vérifier les erreurs d'upload
        if ($file->getError() !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée',
                UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale spécifiée dans le formulaire',
                UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque',
                UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload du fichier'
            ];

            $errorMessage = $errorMessages[$file->getError()] ?? 'Erreur d\'upload inconnue';
            throw new \RuntimeException($errorMessage . ' (Code: ' . $file->getError() . ')');
        }

        // Capturer la taille AVANT de déplacer le fichier
        $fileSize = $file->getSize();

        // Créer le répertoire spécifique à la candidature
        $uploadDir = $this->getCandidatureUploadDir($numCandidature);

        // Générer un nom de fichier sécurisé
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->sanitizeFilename($originalName);
        $fileExtension = $file->guessExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

        $fileName = $type . '_' . $safeFilename . '_' . uniqid() . '.' . $fileExtension;

        // Déplacer le fichier
        try {
            $file->move($uploadDir, $fileName);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors du déplacement du fichier: ' . $e->getMessage());
        }

        // Vérifier que le fichier a été déplacé avec succès
        $fullPath = $uploadDir . $fileName;
        if (!file_exists($fullPath)) {
            throw new \RuntimeException('Le fichier n\'a pas été correctement déplacé.');
        }

        return [
            'path' => '/uploads/candidatures/' . $numCandidature . '/' . $fileName,
            'name' => $file->getClientOriginalName(),
            'size' => $fileSize // Utiliser la taille capturée avant le déplacement
        ];
    }

    public function removeFile(string $filePath): bool
    {
        $fullPath = $this->getProjectDir() . '/public' . $filePath;

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    public function removeCandidatureFolder(string $numCandidature): bool
    {
        $candidatureDir = $this->getCandidatureUploadDir($numCandidature);
        
        if (!file_exists($candidatureDir)) {
            return true;
        }

        return $this->deleteDirectory($candidatureDir);
    }

    public function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    public function getProjectDir(): string
    {
        return $this->params->get('kernel.project_dir');
    }

    public function getUploadDir(): string
    {
        return $this->getProjectDir() . '/public/uploads/candidatures/';
    }

    public function getCandidatureUploadDir(string $numCandidature): string
    {
        $candidatureDir = $this->getUploadDir() . $numCandidature . '/';
        
        // Créer le répertoire s'il n'existe pas
        if (!file_exists($candidatureDir)) {
            if (!mkdir($candidatureDir, 0777, true) && !is_dir($candidatureDir)) {
                throw new \RuntimeException('Impossible de créer le répertoire de candidature: ' . $candidatureDir);
            }
        }

        // Vérifier que le répertoire est accessible en écriture
        if (!is_writable($candidatureDir)) {
            throw new \RuntimeException('Le répertoire de candidature n\'est pas accessible en écriture: ' . $candidatureDir);
        }

        return $candidatureDir;
    }

    private function sanitizeFilename(string $filename): string
    {
        // Vérifier si la fonction transliterator_transliterate est disponible
        if (function_exists('transliterator_transliterate')) {
            // Essayer d'utiliser le transliterator d'ICU
            $cleanFilename = \transliterator_transliterate(
                'Any-Latin; Latin-ASCII; [^A-Za-z0-9_-] remove; Lower()',
                $filename
            );
        }

        // Si le transliterator n'est pas disponible ou a échoué, utiliser une méthode alternative
        if (!function_exists('transliterator_transliterate') || $cleanFilename === false) {
            // Remplacer les caractères accentués par leur équivalent non accentué
            $cleanFilename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename);
            // Remplacer les caractères non alphanumériques par des underscores
            $cleanFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $cleanFilename);
            // Convertir en minuscules
            $cleanFilename = strtolower($cleanFilename);
        }

        // Limiter la longueur du nom de fichier
        $cleanFilename = substr($cleanFilename, 0, 100);

        return $cleanFilename ?: 'file';
    }

    public function validateFile(UploadedFile $file, array $allowedMimeTypes = [], int $maxSize = 3145728): bool
    {
        if (!$file->isValid()) {
            return false;
        }

        // Vérifier la taille
        if ($file->getSize() > $maxSize) {
            throw new \RuntimeException('Le fichier dépasse la taille maximale autorisée de ' . ($maxSize / 1024 / 1024) . 'MB');
        }

        // Vérifier le type MIME si spécifié
        if (!empty($allowedMimeTypes)) {
            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, $allowedMimeTypes, true)) {
                throw new \RuntimeException('Type de fichier non autorisé. Types acceptés: ' . implode(', ', $allowedMimeTypes));
            }
        }

        return true;
    }
}