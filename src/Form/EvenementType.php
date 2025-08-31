<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'événement',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le titre est obligatoire']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Ex: Conférence développement web',
                    'class' => 'form-control'
                ]
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date et heure',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date est obligatoire']),
                    new Assert\GreaterThan([
                        'value' => 'now',
                        'message' => 'La date doit être dans le futur'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu de l\'événement',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le lieu est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le lieu doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le lieu ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Ex: Salle de conférence A',
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 1000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Décrivez votre événement...',
                    'class' => 'form-control'
                ]
            ])
            ->add('participants', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => function(Participant $participant) {
                    return $participant->getPrenom() . ' ' . $participant->getNom() . ' (' . $participant->getEmail() . ')';
                },
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'label' => 'Participants à inscrire',
                'attr' => [
                    'class' => 'form-select',
                    'size' => 5
                ],
                'help' => 'Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs participants'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}