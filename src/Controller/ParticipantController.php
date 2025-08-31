<?php

namespace App\Controller;

use App\Entity\Participant;
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
    #[Route('/', name: 'app_participant_index', methods: ['GET'])]
    public function index(ParticipantRepository $participantRepository): Response
    {
        return $this->render('participant/index.html.twig', [
            'participants' => $participantRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_participant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Synchroniser les relations bidirectionnelles
                foreach ($participant->getEvenements() as $evenement) {
                    $evenement->addParticipant($participant);
                }
                
                $entityManager->persist($participant);
                $entityManager->flush();

                $this->addFlash('success', 
                    'Le participant "' . $participant->getPrenom() . ' ' . $participant->getNom() . '" a été créé avec succès !'
                );
                
                return $this->redirectToRoute('app_participant_show', [
                    'id' => $participant->getId()
                ], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création : ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('warning', 'Le formulaire contient des erreurs. Veuillez vérifier les champs obligatoires.');
        }

        return $this->render('participant/new.html.twig', [
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

    #[Route('/{id}/edit', name: 'app_participant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        // Sauvegarder les événements actuels pour comparaison
        $evenementsOriginaux = clone $participant->getEvenements();
        
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Nettoyer les anciennes relations
                foreach ($evenementsOriginaux as $evenement) {
                    $evenement->removeParticipant($participant);
                }
                
                // Établir les nouvelles relations
                foreach ($participant->getEvenements() as $evenement) {
                    $evenement->addParticipant($participant);
                }
                
                $entityManager->flush();

                $this->addFlash('success', 
                    'Le participant "' . $participant->getPrenom() . ' ' . $participant->getNom() . '" a été modifié avec succès !'
                );
                
                return $this->redirectToRoute('app_participant_show', [
                    'id' => $participant->getId()
                ], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('warning', 'Le formulaire contient des erreurs. Veuillez corriger les champs indiqués.');
        }

        return $this->render('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_delete', methods: ['POST'])]
    public function delete(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $nom = $participant->getPrenom() . ' ' . $participant->getNom();
                
                // Nettoyer les relations avant suppression
                foreach ($participant->getEvenements() as $evenement) {
                    $evenement->removeParticipant($participant);
                }
                
                $entityManager->remove($participant);
                $entityManager->flush();
                
                $this->addFlash('success', 'Le participant "' . $nom . '" a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
        }

        return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
    }
}