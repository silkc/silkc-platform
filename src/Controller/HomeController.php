<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="app_")
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('front/home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/search", name="search")
     */
    public function search(): Response
    {
        return $this->render('front/search/search.html.twig');
    }

    /**
     * @Route("/search_results", name="search_results")
     */
    public function searchResults(): Response
    {


        $trainings = [
            [
                "title" => "accommodation manager",
                "match" => 99,
            ],
            [
                "title" => "art director",
                "match" => 80,
            ],
            [
                "title" => "anim pariatur cliche reprehenderit",
                "match" => 60,
            ],
            [
                "title" => "cred nesciunt sapiente ea proident",
                "match" => 20,
            ],
        ];

        return $this->render('front/search/search_results.html.twig', ['trainings' => $trainings]);
    }
}
