<?php

namespace App\Service;

use Twig\Environment;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class PdfGenerator
{
    private $twig;
    private $filesystem;
    private $logger;

    public function __construct(
        Environment $twig, 
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->twig = $twig;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    public function generate(string $path, string $fileName, string $template, array $data): bool
    {
        try {
            $pdfOptions = new Options();
            $pdfOptions->set([
                'enable_remote' => true,
                'chroot' => '/www/public/',
                'defaultFont' => 'Arial'
            ]);
            
            $dompdf = new Dompdf($pdfOptions);
            $html = $this->twig->render($template, $data);
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            try {
                $fullPath = Path::normalize($path);
                $this->filesystem->mkdir($fullPath);
                file_put_contents($fullPath.'/'.$fileName, $dompdf->output());
                return true;
            } catch (IOExceptionInterface $e) {
                $this->logger->error('Erreur lors de la création du fichier PDF: ' . $e->getMessage());
                throw new \RuntimeException('Impossible de créer le fichier PDF: ' . $e->getMessage(), 0, $e);
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Échec de la génération du PDF: ' . $e->getMessage());
            throw new \RuntimeException('Erreur lors de la génération du PDF: ' . $e->getMessage(), 0, $e);
        }
    }

    public function stream(string $template, array $data): Response
    {
        try {
            $pdfOptions = new Options();
            $pdfOptions->set([
                'enable_remote' => true,
                'chroot' => '/www/public/',
                'defaultFont' => 'Arial'
            ]);
            
            $dompdf = new Dompdf($pdfOptions);
            $html = $this->twig->render($template, $data);
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            return new Response(
                $dompdf->output(),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="document.pdf"'
                ]
            );
            
        } catch (\Exception $e) {
            $this->logger->error('Échec du streaming PDF: ' . $e->getMessage());
            return new Response(
                'Erreur lors de la génération du PDF: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'text/plain']
            );
        }
    }
    
    public function imageToBase64(string $path): ?string 
    {
        try {
            if (!file_exists($path)) {
                $this->logger->warning('Fichier image non trouvé: ' . $path);
                return null;
            }
            
            if (!is_readable($path)) {
                $this->logger->warning('Droits insuffisants pour lire le fichier: ' . $path);
                return null;
            }
            
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            
            if ($data === false) {
                $this->logger->warning('Impossible de lire le contenu du fichier: ' . $path);
                return null;
            }
            
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
            
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la conversion de l\'image en base64: ' . $e->getMessage());
            return null;
        }
    }
}