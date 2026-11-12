<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/post')]
final class PostController extends AbstractController
{
    #[Route(name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_AUTEUR")]
    public function new(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        $user = $this->getUser();
        $date = new \DateTimeImmutable("now");

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCreatedAt($date);
            $post->setAuthor($user);
            $image = $form->get('image')->getData();

            if ($image) {
                $filename = $fileUploader->upload($image);
                $post->setImage($filename);
            }

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash("success","Votre article à été créé avec succès");

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager, FileUploader $fileUploader, Filesystem $filesystem): Response
    {
        $this->denyAccessUnlessGranted('POST_EDIT', $post);
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldImage = $post->getImage();
            $image = $form->get('image')->getData();
            if ($image) {
                $filename = $fileUploader->upload($image);
                if ($oldImage) {
                    $oldFilePath = $this->getParameter('images_directory').'/'.$oldImage;
                    if ($filesystem->exists($oldFilePath)) {
                        $filesystem->remove($oldFilePath);
                    }
                }
                $post->setImage($filename);
            }
            $entityManager->flush();

            $this->addFlash("success","Votre article à bien été mis à jour");

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager, FileUploader $fileUploader, Filesystem $filesystem): Response
    {
        $this->denyAccessUnlessGranted('POST_EDIT', $post);
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->getPayload()->getString('_token'))) {
            $oldImage = $post->getImage();
            if ($oldImage) {
                $oldFilePath = $this->getParameter('images_directory').'/'.$oldImage;
                if ($filesystem->exists($oldFilePath)) {
                    $filesystem->remove($oldFilePath);
                }
            }

            $entityManager->remove($post);
            $entityManager->flush();
            
            $this->addFlash("success","Votre article à bien été supprimé");
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
