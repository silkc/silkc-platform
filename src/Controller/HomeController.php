<?php

namespace App\Controller;

use App\Repository\OccupationRepository;
use App\Repository\TrainingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        return $this->render('front/search/search.html.twig');
    }

    /**
     * @Route("/search_results", name="search_results")
     */
    public function searchResults(Request $request, OccupationRepository $occupationRepository, TrainingRepository $trainingRepository): Response
    {
        $occupationId = $request->request->get('hidden_training_search');
        if (!$occupationId) {

        }
        $occupation = $occupationRepository->findOneBy(['id' => $occupationId]);
        if (!$occupation) {

        }

        /*$trainings = [
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
        ];*/

        $trainings = $trainingRepository->findBy(['occupation' => $occupation]);
        dd($trainings);

        return $this->render('front/search/search_results.html.twig', ['trainings' => $trainings]);
    }
}
