<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'label_attr' => [
                    'class' => 'user_label'
                ],
                'attr' => [
                    'class' => 'user_input'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Lecteur' => 'ROLE_USER',
                    'Auteur' => 'ROLE_AUTEUR',
                    'Administrateur' => 'ROLE_ADMIN'
                ],
                'multiple' => true,
                'expanded' => true
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
