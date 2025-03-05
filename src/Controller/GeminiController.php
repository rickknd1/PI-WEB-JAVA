<?php

namespace App\Controller;

use App\Service\CommunityDescriptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GeminiController extends AbstractController
{
    private CommunityDescriptionService $descriptionService;

    public function __construct(CommunityDescriptionService $descriptionService)
    {
        $this->descriptionService = $descriptionService;
    }

    #[Route('/generate-description', name: 'generate_description', methods: ['POST'])]
    public function generateDescription(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $title = $data['title'] ?? '';
        $keywords = $data['keywords'] ?? '';

        if (empty($keywords)) {
            return new JsonResponse(['error' => 'Mots-clés requis.'], 400);
        }

        $generatedText = $this->descriptionService->traiterDemande("Génère une mini description pour une communauté basée sur ces mots-clés: $title , $keywords (juste une seule description)(je veut que tu parle a des evenements et des chatrooms)");

        return new JsonResponse(['description' => $generatedText]);
    }
    #[Route('/generate-description-event', name: 'generate_description_event', methods: ['POST'])]
    public function generateDescriptionEvent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $Nom = $data['Nom'] ?? '';
        $date = $data['date'] ?? '';
        $lieu = $data['lieu'] ?? '';

        if (empty($Nom)) {
            return new JsonResponse(['error' => 'Nom requis.'], 400);
        }
        if (empty($date)) {
            return new JsonResponse(['error' => 'Date requis.'], 400);
        }
        if (empty($lieu)) {
            return new JsonResponse(['error' => 'Lieu requis.'], 400);
        }

        $generatedText = $this->descriptionService->traiterDemande("Génère une mini description pour un evenement . le nom de levenement : $Nom , la date evenement commance : $date , lieu de levenement : $lieu");

        return new JsonResponse(['description' => $generatedText]);
    }
}
