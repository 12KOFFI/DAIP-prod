<?php

namespace App\Controller;

use App\Entity\CfaEtablissement;
use App\Form\CfaEtablissementType;
use App\Repository\CfaEtablissementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cfa/etablissement')]
#[IsGranted('ROLE_ADMIN')]
final class CfaEtablissementController extends AbstractController
{
    #[Route(name: 'app_cfa_etablissement_index', methods: ['GET'])]
    public function index(CfaEtablissementRepository $cfaEtablissementRepository): Response
    {
        $cfaEtablissements = $cfaEtablissementRepository->createQueryBuilder('c')
            ->leftJoin('c.filieres', 'f')
            ->addSelect('f')
            ->leftJoin('c.cfaMetiers', 'cm')
            ->addSelect('cm')
            ->leftJoin('cm.metier', 'm')
            ->addSelect('m')
            ->getQuery()
            ->getResult();

        return $this->render('cfa_etablissement/index.html.twig', [
            'cfa_etablissements' => $cfaEtablissements,
        ]);
    }

    #[Route('/new', name: 'app_cfa_etablissement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CfaEtablissementRepository $cfaEtablissementRepository): Response
    {
        $cfaEtablissement = new CfaEtablissement();
        
        // Générer le numéro d'établissement CFA
        $lastCfaEtablissement = $cfaEtablissementRepository->findOneBy([], ['id' => 'DESC']);
        $nextId = $lastCfaEtablissement ? $lastCfaEtablissement->getId() + 1 : 1;
        $cfaEtablissement->setNumcfaEtablissement('CFA-' . str_pad($nextId, 4, '0', STR_PAD_LEFT));
        
        $form = $this->createForm(CfaEtablissementType::class, $cfaEtablissement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cfaEtablissement);
            $entityManager->flush();

            $this->addFlash('success', 'L\'établissement CFA a été créé avec succès.');
            return $this->redirectToRoute('app_cfa_etablissement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cfa_etablissement/_form.html.twig', [
            'cfa_etablissement' => $cfaEtablissement,
            'form' => $form,
            'title' => 'Nouveau cfa etablissement',
        ]);
    }

    #[Route('/{id}', name: 'app_cfa_etablissement_show', methods: ['GET'])]
    public function show(CfaEtablissement $cfaEtablissement): Response
    {
        return $this->render('cfa_etablissement/show.html.twig', [
            'cfa_etablissement' => $cfaEtablissement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cfa_etablissement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CfaEtablissement $cfaEtablissement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CfaEtablissementType::class, $cfaEtablissement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cfa_etablissement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cfa_etablissement/_form.html.twig', [
            'cfa_etablissement' => $cfaEtablissement,
            'form' => $form,
            'title' => 'Modifier le cfa etablissement',
        ]);
    }

    #[Route('/{id}', name: 'app_cfa_etablissement_delete', methods: ['POST'])]
    public function delete(Request $request, CfaEtablissement $cfaEtablissement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cfaEtablissement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cfaEtablissement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cfa_etablissement_index', [], Response::HTTP_SEE_OTHER);
    }
}
