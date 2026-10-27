<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfilPasswordType;
use App\Form\ProfilUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MyProfilController extends AbstractController
{
    #[Route('/myprofil', name: 'app_my_profil')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): Response
    {
        return $this->render('my_profil/index.html.twig', [
            'controller_name' => 'MyProfilController',
        ]);
    }

    #[Route('/myprofil/{id}', name: 'app_profil_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($user->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ces informations');
        }
        $form = $this->createForm(ProfilUserType::class, $user);
        $form->remove('password');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_my_profil', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('my_profil/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/myprofil/password/{id}', name: 'app_profil_password_edit', methods: ['GET', 'POST'])]
    public function editPassword(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if ($user->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce mot de passe');
        }
        $form = $this->createForm(ProfilPasswordType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('oldPassword')->getData();
            if (!$userPasswordHasher->isPasswordValid($user, $oldPassword)) {
                $form->get('oldPassword')->addError(
                    new FormError("Votre ancien mot de passe est incorrect")
                );
            }
            else {
                /** @var string $plainPassword */
                $plainPassword = $form->get('password')->getData();
    
                // encode the plain password
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
    
                $entityManager->flush();
    
                return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('my_profil/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
