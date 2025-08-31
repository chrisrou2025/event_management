<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(EvenementRepository $evenementRepository): Response
    {
        // Récupérer les événements à venir (date >= aujourd'hui)
        $evenementsAvenir = $evenementRepository->findUpcomingEvents();
        
        // Récupérer les 3 derniers événements ajoutés
        $derniersEvenements = $evenementRepository->findBy(
            [],
            ['id' => 'DESC'],
            3
        );
        
        // Compter le nombre total d'événements et participants
        $totalEvenements = $evenementRepository->count([]);
        $totalParticipants = $this->getTotalParticipants($evenementRepository);

        return $this->render('homepage/index.html.twig', [
            'evenements_avenir' => $evenementsAvenir,
            'derniers_evenements' => $derniersEvenements,
            'total_evenements' => $totalEvenements,
            'total_participants' => $totalParticipants,
        ]);
    }

    /**
     * Calculer le nombre total de participants uniques
     */
    private function getTotalParticipants(EvenementRepository $evenementRepository): int
    {
        // Cette méthode compte les participants uniques
        // Nous l'implémentons dans le repository
        return $evenementRepository->countUniqueParticipants();
    }
}