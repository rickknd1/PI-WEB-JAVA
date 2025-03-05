<?php

// src/Controller/VilleController.php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Entity\LieuCulturels;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Media;
use App\Entity\MediaRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VilleController extends AbstractController
{
    private $httpClient;
    private $apiKey = "1e6b8880d365462f9c4102051250503";
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    #[Route('/admin/ville', name: 'ville_index', methods: ['GET', 'POST'])]
    public function index(VilleRepository $villeRepository, PaginatorInterface $paginator, Request $request,EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $villes = $paginator->paginate(
            $villeRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            return $this->redirectToRoute('ville_index');
        }

        return $this->render('ville/listeville.html.twig', [
            'villes' => $villes,
            'ville' => $ville,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }


    #[Route('/front/ville', name: 'ville_index_front', methods: ['GET', 'POST'])]
    public function index2(VilleRepository $villeRepository, PaginatorInterface $paginator, Request $request,EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $villes = $paginator->paginate(
            $villeRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            return $this->redirectToRoute('ville_index_front');
        }

        return $this->render('ville/listvillefront.html.twig', [
            'villes' => $villes,
            'ville' => $ville,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    // #[Route('/ville/{id}', name: 'ville_show', methods: ['GET'])]
    // public function show(Ville $ville, EntityManagerInterface $entityManager): Response
    // {
    //     // Filtrer les lieux culturels par ville (en fonction de l'ID de la ville)
    //     $lieuCulturels = $entityManager
    //         ->getRepository(LieuCulturels::class)
    //         ->findBy(['ville' => $ville->getId()]); // Assurez-vous que la relation est définie
    
    //     return $this->render('ville/detailville.html.twig', [
    //         'ville' => $ville,
    //         'lieu_culturels' => $lieuCulturels,
    //     ]);
    // }
    #[Route('/ville/{id<\d+>}', name: 'ville_show', methods: ['GET'])]
public function show(int $id, VilleRepository $villeRepository, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    $ville = $villeRepository->find($id);

    if (!$ville) {
        throw $this->createNotFoundException("Ville avec ID $id non trouvée.");
    }

    $lieuCulturels = $entityManager
        ->getRepository(LieuCulturels::class)
        ->findBy(['ville' => $ville->getId()]);

    return $this->render('ville/detailville.html.twig', [
        'ville' => $ville,
        'lieu_culturels' => $lieuCulturels,
        'user' => $user,
    ]);
}


    #[Route('front/ville/{id}', name: 'ville_show_front', methods: ['GET'])]
    public function show2(Ville $ville, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // Filtrer les lieux culturels par ville (en fonction de l'ID de la ville)
        $lieuCulturels = $entityManager
            ->getRepository(LieuCulturels::class)
            ->findBy(['ville' => $ville->getId()]); // Assurez-vous que la relation est définie
    
        return $this->render('ville/detailsvillefront.twig', [
            'ville' => $ville,
            'lieu_culturels' => $lieuCulturels,
            'user' => $user,
        ]);
    }
    

    #[Route('/admin/ville/{id}/edit', name: 'ville_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ville $ville, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('ville_index');
        }

        return $this->render('ville/modifierville.html.twig', [
            'ville' => $ville,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/admin/ville/{id}', name: 'ville_delete', methods: ['POST'])]
    public function delete(Request $request, Ville $ville, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete' . $ville->getId(), $request->request->get('_token'))) {
            // Récupérer les lieux culturels associés à cette ville
            $lieuxCulturels = $ville->getLieux();
    
            foreach ($lieuxCulturels as $lieu) {
                // Supprimer les médias associés à chaque lieu culturel
                $medias = $lieu->getMedia(); // Assurez-vous que la relation existe dans l'entité LieuCulturels
                foreach ($medias as $media) {
                    $entityManager->remove($media);
                }
    
                // Supprimer le lieu culturel
                $entityManager->remove($lieu);
            }
    
            // Supprimer la ville
            $entityManager->remove($ville);
            $entityManager->flush();
    
            // Rediriger vers l'index des villes
            return $this->redirectToRoute('ville_index');
        }
    
        
        throw $this->createAccessDeniedException('Token CSRF invalide.');
    }

    #[Route('/ville/search', name: 'ville_search', methods: ['GET'])]
    public function search(Request $request, VilleRepository $villeRepository): JsonResponse
    {
        $user = $this->getUser();
        $query = $request->query->get('search', '');
    
        // If no search query, return all cities
        if (empty($query)) {
            $villes = $villeRepository->findAll();
        } else {
            $villes = $villeRepository->createQueryBuilder('v')
                ->where('v.nom LIKE :query')
                ->orWhere('v.description LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->getQuery()
                ->getResult();
        }
    
        // Convert objects to arrays for JSON response
        $response = [];
        foreach ($villes as $ville) {
            $response[] = [
                'id' => $ville->getId(),
                'nom' => $ville->getNom(),
                'description' => $ville->getDescription(),
                'position' => $ville->getPosition(),
                'user' => $user
            ];
        }
    
        return $this->json($response);
    }
    #[Route('/calendar/events', name: 'calendar_events', methods: ['GET'])]
    public function fetchEvents(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        $villes = $entityManager->getRepository(Ville::class)->findAll();
        
        $events = [];
        foreach ($villes as $ville) {
            $events[] = [
                'id' => $ville->getId(),
                'title' => $ville->getNom(),
                'start' => date('Y-m-d'), // You need a real date field here!
                'description' => $ville->getDescription(),
                'user' => $user
            ];
        }

        return $this->json($events);
    }
    #[Route('/calendar', name: 'calendar_view', methods: ['GET'])]
public function viewCalendar(): Response
{
    return $this->render('calendar.html.twig');
}
#[Route('/ville/weather/{id}', name: 'ville_weather', methods: ['GET'])]
public function getWeather(int $id, EntityManagerInterface $em): JsonResponse
{
    $user = $this->getUser();

    $ville = $em->getRepository(Ville::class)->find($id);

    if (!$ville) {
        return new JsonResponse(['error' => 'Ville not found'], 404);
    }

    $cityName = $ville->getNom();

    // Get Latitude and Longitude from Open-Meteo Geocoding API
    $geoUrl = "https://geocoding-api.open-meteo.com/v1/search?name=" . urlencode($cityName) . "&count=1";

    try {
        $geoResponse = $this->httpClient->request('GET', $geoUrl);
        $geoData = $geoResponse->toArray();

        if (!isset($geoData['results'][0])) {
            return new JsonResponse(['error' => 'Location not found'], 404);
        }

        $latitude = $geoData['results'][0]['latitude'];
        $longitude = $geoData['results'][0]['longitude'];

        // Fetch weather data
        $weatherUrl = "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&current_weather=true";
        $weatherResponse = $this->httpClient->request('GET', $weatherUrl);
        $weatherData = $weatherResponse->toArray();

        return new JsonResponse([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'temperature' => $weatherData['current_weather']['temperature'],
            'windspeed' => $weatherData['current_weather']['windspeed'],
            'weathercode' => $weatherData['current_weather']['weathercode']
        ]);
    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Failed to fetch weather'], 500);
    }
}
#[Route('/villes', name: 'ville_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        $villes = $em->getRepository(Ville::class)->findAll();
        $data = [];

        foreach ($villes as $ville) {
            $cityName = $ville->getNom();

           
            $geoUrl = "https://geocoding-api.open-meteo.com/v1/search?name=" . urlencode($cityName) . "&count=1";

            try {
                $geoResponse = $this->httpClient->request('GET', $geoUrl);
                $geoData = $geoResponse->toArray();

                if (isset($geoData['results'][0])) {
                    $latitude = $geoData['results'][0]['latitude'];
                    $longitude = $geoData['results'][0]['longitude'];
                } else {
                    $latitude = null;
                    $longitude = null;
                }
            } catch (\Exception $e) {
                $latitude = null;
                $longitude = null;
            }

            $data[] = [
                'id' => $ville->getId(),
                'nom' => $ville->getNom(),
                'description' => $ville->getDescription(),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'user'=>$user
            ];
        }

        return new JsonResponse($data);
    }

    
    
}