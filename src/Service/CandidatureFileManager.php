<?php

namespace App\Service;

use App\Entity\Candidature;
use App\Entity\PieceJointe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CandidatureFileManager
{
    private $fileUploader;
    private $entityManager;

    public function __construct(FileUploader $fileUploader, EntityManagerInterface $entityManager)
    {
        $this->fileUploader = $fileUploader;
        $this->entityManager = $entityManager;
    }

    public function processCandidatureFiles(FormInterface $form, Candidature $candidature): void
    {
        // S'assurer que la candidature a un numéro définitif
        if (null === $candidature->getNumCandidature() || str_starts_with($candidature->getNumCandidature(), 'TEMP_')) {
            throw new \RuntimeException('La candidature doit avoir un numéro définitif avant de pouvoir y attacher des fichiers.');
        }

        $fileFields = [
            'medias_piece' => 'CNI',
            'medias_extrait' => 'EXTRACT_NAISSANCE',
            'medias_niveau' => 'DIPLOME',
            'medias_cmu' => 'CMU',
            'medias_photo' => 'PHOTO'
        ];

        // Parcourir chaque champ de fichier
        foreach ($fileFields as $fieldName => $type) {
            $uploadedFile = $form->get($fieldName)->getData();
            
            if ($uploadedFile instanceof UploadedFile) {
                $this->handleFileUpload($uploadedFile, $type, $candidature);
            }
        }
    }

    private function determineFileType(UploadedFile $file, FormInterface $form): ?string
    {
        // This is a simplified example - you might need to adjust based on your form structure
        // You could also use the file's name, MIME type, or other attributes to determine the type
        $fieldName = $file->getClientOriginalName();
        
        if (str_contains(strtolower($fieldName), 'piece') || str_contains(strtolower($fieldName), 'cni')) {
            return 'CNI';
        } elseif (str_contains(strtolower($fieldName), 'extrait') || str_contains(strtolower($fieldName), 'naissance')) {
            return 'EXTRACT_NAISSANCE';
        } elseif (str_contains(strtolower($fieldName), 'diplome') || str_contains(strtolower($fieldName), 'niveau')) {
            return 'DIPLOME';
        } elseif (str_contains(strtolower($fieldName), 'photo') || in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
            return 'PHOTO';
        }
        
        return null;
    }

    private function handleFileUpload(UploadedFile $file, string $type, Candidature $candidature): void
    {
        try {
            // Vérifications initiales cruciales
            if (!$file instanceof UploadedFile) {
                throw new \RuntimeException('Le fichier n\'est pas une instance valide de UploadedFile');
            }

            // Valider le fichier avant tout traitement
            $this->validateFileBeforeProcessing($file, $type);

            // Upload du fichier dans le dossier de la candidature
            $fileInfo = $this->fileUploader->upload($file, $candidature->getNumCandidature(), $type);
            
            // Vérifier si une pièce jointe de ce type existe déjà
            $existingPiece = $this->findExistingPiece($candidature, $type);
            
            if ($existingPiece) {
                $this->replaceExistingFile($existingPiece, $fileInfo);
            } else {
                $this->createNewPiece($fileInfo, $type, $candidature);
            }

        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur avec le fichier "' . $file->getClientOriginalName() . '": ' . $e->getMessage());
        }
    }

    private function validateFileBeforeProcessing(UploadedFile $file, string $type): void
    {
        // Vérifier que le fichier est valide
        if (!$file->isValid()) {
            throw new \RuntimeException('Fichier invalide. Erreur: ' . $file->getError());
        }

        // Vérifier les erreurs d'upload
        if ($file->getError() !== UPLOAD_ERR_OK) {
            $errorMessage = $this->getUploadErrorMessage($file->getError());
            throw new \RuntimeException($errorMessage);
        }

        // Vérifier la taille maximale selon le type de fichier
        $maxSize = $this->getMaxSizeForType($type);
        if ($file->getSize() > $maxSize) {
            throw new \RuntimeException('Le fichier dépasse la taille maximale de ' . ($maxSize / 1024 / 1024) . 'MB autorisée pour ' . $type);
        }

        // Vérifier le type MIME selon le type de fichier
        $allowedMimeTypes = $this->getAllowedMimeTypesForType($type);
        if (!empty($allowedMimeTypes)) {
            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, $allowedMimeTypes, true)) {
                throw new \RuntimeException('Type de fichier non autorisé pour ' . $type . '. Types acceptés: ' . implode(', ', $allowedMimeTypes));
            }
        }
    }

    private function getMaxSizeForType(string $type): int
    {
        // Taille maximale de 5 Mo pour tous les types de fichiers
        return 5 * 1024 * 1024; // 5MB pour tous les types
    }

    private function getAllowedMimeTypesForType(string $type): array
    {
        $mimeTypes = [
            'CNI' => ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'],
            'EXTRACT_NAISSANCE' => ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'],
            'DIPLOME' => ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'],
            'CMU' => ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'],
            'PHOTO' => ['image/jpeg', 'image/png', 'image/jpg'],
        ];

        return $mimeTypes[$type] ?? [];
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par le serveur',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale spécifiée dans le formulaire',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant sur le serveur',
            UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque du serveur',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload du fichier',
        ];

        return $errorMessages[$errorCode] ?? 'Erreur d\'upload inconnue (Code: ' . $errorCode . ')';
    }

    private function findExistingPiece(Candidature $candidature, string $type): ?PieceJointe
    {
        return $this->entityManager->getRepository(PieceJointe::class)
            ->findOneBy([
                'candidature' => $candidature,
                'type' => $type
            ]);
    }

    private function replaceExistingFile(PieceJointe $piece, array $fileInfo): void
    {
        try {
            // Supprimer l'ancien fichier s'il existe
            if ($piece->getChemin() && file_exists($this->getProjectDir() . '/public' . $piece->getChemin())) {
                $this->fileUploader->removeFile($piece->getChemin());
            }
        } catch (\Exception $e) {
            // Logger l'erreur mais continuer
            error_log('Erreur lors de la suppression de l\'ancien fichier: ' . $e->getMessage());
        }

        // Mettre à jour la pièce jointe existante
        $piece->setChemin($fileInfo['path']);
        $piece->setNom($fileInfo['name']);
        $piece->setTailleFichier($fileInfo['size']);
        $piece->setDateModification(new \DateTime());

        $this->entityManager->persist($piece);
    }

    private function createNewPiece(array $fileInfo, string $type, Candidature $candidature): void
    {
        $pieceJointe = new PieceJointe();
        $pieceJointe->setType($type);
        $pieceJointe->setChemin($fileInfo['path']);
        $pieceJointe->setNom($fileInfo['name']);
        $pieceJointe->setTailleFichier($fileInfo['size']);
        $pieceJointe->setNumCandidature($candidature->getNumCandidature());
        $pieceJointe->setCandidature($candidature);
        $pieceJointe->setDateCreation(new \DateTime());

        $this->entityManager->persist($pieceJointe);
    }

    public function removeCandidatureFiles(Candidature $candidature): void
    {
        $pieces = $candidature->getPieceJointe();
        
        foreach ($pieces as $piece) {
            try {
                // Supprimer le fichier physique
                if ($piece->getChemin()) {
                    $this->fileUploader->removeFile($piece->getChemin());
                }
                // Supprimer l'entité
                $this->entityManager->remove($piece);
            } catch (\Exception $e) {
                // Logger l'erreur mais continuer la suppression
                error_log('Erreur lors de la suppression du fichier: ' . $e->getMessage());
                continue;
            }
        }

        // Supprimer le dossier de la candidature après suppression des fichiers
        try {
            $this->fileUploader->removeCandidatureFolder($candidature->getNumCandidature());
        } catch (\Exception $e) {
            error_log('Erreur lors de la suppression du dossier de candidature: ' . $e->getMessage());
        }
    }

    public function getProjectDir(): string
    {
        return $this->fileUploader->getProjectDir();
    }

    public function cleanupOrphanedFiles(): void
    {
        // Méthode optionnelle pour nettoyer les fichiers orphelins
        $uploadDir = $this->fileUploader->getUploadDir();
        $allFolders = glob($uploadDir . '*', GLOB_ONLYDIR);
        
        foreach ($allFolders as $folderPath) {
            if (is_dir($folderPath) && filemtime($folderPath) < strtotime('-1 day')) {
                // Supprimer les dossiers vieux de plus d'un jour
                $this->fileUploader->deleteDirectory($folderPath);
            }
        }
    }

    public function initializeCandidatureFolder(string $numCandidature): void
    {
        // Créer le dossier de la candidature à l'initialisation
        $this->fileUploader->getCandidatureUploadDir($numCandidature);
    }
}
