<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(Request $request, EvenementRepository $evenementRepository): Response
    {
        $query = $request->query->get('query', '');

        if ($query) {
            // Tenter de convertir la requête en date
            try {
                $date = \DateTime::createFromFormat('d/m/Y', $query);
                if ($date) {
                    $evenementsAvenir = $evenementRepository->findByDate($date);
                } else {
                    $evenementsAvenir = $evenementRepository->findByLieu($query);
                }
            } catch (\Exception $e) {
                // Si la conversion en date échoue, rechercher par lieu
                $evenementsAvenir = $evenementRepository->findByLieu($query);
            }
            $derniersEvenements = [];
        } else {
            $evenementsAvenir = $evenementRepository->findUpcomingEvents();
            $derniersEvenements = $evenementRepository->findBy([], ['id' => 'DESC'], 3);
        }

        $totalEvenements = $evenementRepository->count([]);
        $totalParticipants = $this->getTotalParticipants($evenementRepository);

        return $this->render('homepage/index.html.twig', [
            'evenements_avenir' => $evenementsAvenir,
            'derniers_evenements' => $derniersEvenements,
            'total_evenements' => $totalEvenements,
            'total_participants' => $totalParticipants,
            'search_query' => $query,
        ]);
    }

    private function getTotalParticipants(EvenementRepository $evenementRepository): int
    {
        return $evenementRepository->countUniqueParticipants();
    }
}