<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_liste_projet');
        }

        if (
            $this->isGranted('ROLE_USER_CENTRE')
            && !$this->isGranted('ROLE_ADMIN')
            && !$this->isGranted('ROLE_JURY')
        ) {
            return $this->redirectToRoute('app_dashboard_centre');
        }

        if (
            $this->isGranted('ROLE_CANDIDAT')
            && !$this->isGranted('ROLE_ADMIN')
            && !$this->isGranted('ROLE_JURY')
        ) {
            return $this->redirectToRoute('app_candidature_index');
        }

        return $this->redirectToRoute('app_liste_projet');
    }
}
