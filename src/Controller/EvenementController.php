<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/evenement')]
final class EvenementController extends AbstractController
{
    #[Route(name: 'app_evenement_index', methods: ['GET'])]
    public function index(EvenementRepository $evenementRepository): Response
    {
        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Synchroniser les relations bidirectionnelles
                foreach ($evenement->getParticipants() as $participant) {
                    $participant->addEvenement($evenement);
                }

                $entityManager->persist($evenement);
                $entityManager->flush();

                $this->addFlash('success', 'L\'événement "' . $evenement->getTitre() . '" a été créé avec succès !');

                return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création de l\'événement : ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('warning', 'Le formulaire contient des erreurs. Veuillez vérifier les champs obligatoires.');
        }

        return $this->render('evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        // Sauvegarder les participants actuels pour comparaison
        $participantsOriginaux = clone $evenement->getParticipants();
        
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Nettoyer les anciennes relations
                foreach ($participantsOriginaux as $participant) {
                    $participant->removeEvenement($evenement);
                }
                
                // Établir les nouvelles relations
                foreach ($evenement->getParticipants() as $participant) {
                    $participant->addEvenement($evenement);
                }

                $entityManager->flush();

                $this->addFlash('success', 'L\'événement "' . $evenement->getTitre() . '" a été modifié avec succès !');

                return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('warning', 'Le formulaire contient des erreurs. Veuillez corriger les champs indiqués.');
        }

        return $this->render('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $titre = $evenement->getTitre();
                
                // Nettoyer les relations avant suppression
                foreach ($evenement->getParticipants() as $participant) {
                    $participant->removeEvenement($evenement);
                }
                
                $entityManager->remove($evenement);
                $entityManager->flush();

                $this->addFlash('success', 'L\'événement "' . $titre . '" a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }
}