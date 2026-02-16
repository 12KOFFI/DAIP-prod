<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Repository\CandidatureRepository;
use App\Service\PdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class PrintInscriptionController extends AbstractController
{
   #[Route('/print/inscription', name: 'app_print_inscription', methods: ['GET'])]
    public function printInscription(
        Request $request,
        CandidatureRepository $candidatureRepository,
        PdfGenerator $pdf
    ): Response
{
    $numero = $request->query->get('numero');

    if (!$numero) {
        $this->addFlash('error', 'Numéro de candidature manquant');
        return $this->redirectToRoute('app_candidature_index');
    }

    $candidature = $candidatureRepository->findOneBy(['numCandidature' => $numero]);

    if (!$candidature) {
        $this->addFlash('error', 'Candidature non trouvée');
        return $this->redirectToRoute('app_candidature_index');
    }
 
    // Récupération image d'entête avec gestion d'erreur
    $entetePath = $this->getParameter('kernel.project_dir') . '/public/front/image/entete.png';
    $enteteImage = $pdf->imageToBase64($entetePath);
  

    // Récupération photo du candidat
    $photoImage = null;
    $photo = $candidature->getPieceJointe()->filter(fn($piece) => $piece->getType() === 'PHOTO')->first();
    
    if ($photo && $photo->getChemin()) {
        $photoPath = $this->getParameter('kernel.project_dir') . '/public/' . ltrim($photo->getChemin(), '/');
        
        if (file_exists($photoPath)) {
            $photoImage = $pdf->imageToBase64($photoPath);
        }
    }
    
    // Si pas de photo, on ne met aucune image
    // (Le template gérera l'affichage d'un message 'Photo non disponible')

    $recrutement = $candidature->getRecrutement();

    $data = [
        'candidature' => $candidature,
        'recrutement' => [
            'nom' => $recrutement ? $recrutement->getLibelle() : 'Recrutement',
            'entreprise' => 'e2c',
        ],
        'image' => [
            'entete' => $enteteImage,
            'photo'  => $photoImage,
        ],
    ];

    return $pdf->stream('print/printIns.html.twig', $data);
}
}
