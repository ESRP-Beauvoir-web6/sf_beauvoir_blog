<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function search(Request $request, PostRepository $postRepository): Response
    {
        $search = trim($request->query->getString('search', ''));
        $posts = [];

        if ($search !== '') {
            $posts = $postRepository->searchByTitle($search);
        }
        return $this->render('search/search.html.twig', [
            'posts' => $posts,
            'search' => $search
        ]);
    }
}
