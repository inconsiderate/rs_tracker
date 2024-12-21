<?php

namespace App\Form;

use App\Entity\StoryTracker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoryTrackerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('storyName', TextType::class, [
                'label' => 'Story Name',
            ])
            ->add('trackedGenres', CollectionType::class, [
                'entry_type' => ChoiceType::class,  // Use ChoiceType for select options
                'entry_options' => [
                    'choices' => [
                        'Action' => 'action',
                        'Adventure' => 'adventure',
                        'Comedy' => 'comedy',
                        'Contemporary' => 'contemporary',
                        'Drama' => 'drama',
                        'Fantasy' => 'fantasy',
                        'Historical' => 'historical',
                        'Horror' => 'horror',
                        'Mystery' => 'mystery',
                        'Psychological' => 'psychological',
                        'Romance' => 'romance',
                        'Satire' => 'satire',
                        'Sci-fi' => 'sci_fi',
                        'Short Story' => 'one_shot',
                        'Tragedy' => 'tragedy',
                    ],
                    'multiple' => true,  // Allow multiple selections
                    'expanded' => false, // Use a dropdown rather than checkboxes
                ],
                'allow_add' => true, // Allow adding genres
                'allow_delete' => true, // Allow deleting genres
                'by_reference' => false, // Ensure it works correctly with the array
                'label' => 'Tracked Genres',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StoryTracker::class,
        ]);
    }
}
