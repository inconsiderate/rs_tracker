<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ChangeUsernameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'New Username',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a username.',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Enter new username',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Change Username',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }
}
