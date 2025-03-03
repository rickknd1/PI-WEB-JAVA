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

        $generatedText = $this->descriptionService->traiterDemande("Génère une mini description pour une communauté basée sur ces mots-clés: $title , $keywords (juste une seule description)(je veut que tu parle a des evenements et des chatrooms");

        return new JsonResponse(['description' => $generatedText]);
    }
}
