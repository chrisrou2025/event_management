<?php
// src/Controller/ParticipantController.php - Méthodes corrigées

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Evenement;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/participant')]
class ParticipantController extends AbstractController
{
    #[Route('/new', name: 'app_participant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Importante : synchroniser la relation des deux côtés
            foreach ($participant->getEvenements() as $evenement) {
                $evenement->addParticipant($participant);
            }
            
            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Le participant a été créé avec succès.');
            
            return $this->redirectToRoute('app_participant_show', [
                'id' => $participant->getId()
            ]);
        }

        return $this->render('participant/new.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        // Sauvegarder les événements actuels pour comparaison
        $evenementsOriginaux = clone $participant->getEvenements();
        
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Supprimer les anciennes relations
            foreach ($evenementsOriginaux as $evenement) {
                $evenement->removeParticipant($participant);
            }
            
            // Ajouter les nouvelles relations
            foreach ($participant->getEvenements() as $evenement) {
                $evenement->addParticipant($participant);
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'Le participant a été modifié avec succès.');
            
            return $this->redirectToRoute('app_participant_show', [
                'id' => $participant->getId()
            ]);
        }

        return $this->render('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_show', methods: ['GET'])]
    public function show(Participant $participant): Response
    {
        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/', name: 'app_participant_index', methods: ['GET'])]
    public function index(ParticipantRepository $participantRepository): Response
    {
        return $this->render('participant/index.html.twig', [
            'participants' => $participantRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_participant_delete', methods: ['POST'])]
    public function delete(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->request->get('_token'))) {
            // Supprimer les relations avant de supprimer le participant
            foreach ($participant->getEvenements() as $evenement) {
                $evenement->removeParticipant($participant);
            }
            
            $entityManager->remove($participant);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le participant a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_participant_index');
    }
}