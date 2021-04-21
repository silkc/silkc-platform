<?php

namespace App\Controller;

use App\Repository\OccupationRepository;
use App\Entity\User;
use App\Repository\SkillRepository;
use App\Repository\TrainingRepository;
use App\Repository\UserRepository;
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
    public function index(TrainingRepository $trainingRepository, OccupationRepository $occupationRepository, SkillRepository $skillRepository): Response
    {
        /*$occupation = $occupationRepository->findOneBy(['id' => 92]);
        $skill = $skillRepository->findOneBy(['id' => 3]);
        $result = $trainingRepository->searchTrainingByOccupation($occupation);*/
        //$res = $trainingRepository->searchTrainingBySkill($skill);

        return $this->render('front/search/search.html.twig');
    }

    /**
     * @Route("/search_results", name="search_results")
     */
    public function searchResults(Request $request, OccupationRepository $occupationRepository, TrainingRepository $trainingRepository, SkillRepository $skillRepository): Response
    {        
        $type_search = $request->get('type_search');
        $trainings = [];
        $search = [];
        
        if ($type_search) {
            $search['type_search'] = $type_search;
            
            switch ($type_search) {
                case 'occupation':
                        $occupation_id = $request->get('hidden_training_search_by_occupation');
                        $occupation_name = $request->get('training_search_by_occupation');
                        if ($occupation_id && $occupation_name) {
                            $search['id'] = $occupation_id;
                            $search['name'] = $occupation_name;
                            $occupation = $occupationRepository->findOneBy(['id' => $occupation_id]);
                            $trainings = $trainingRepository->searchTrainingByOccupation($occupation);
                        }
                    break;
                case 'skill':
                        $skill_id = $request->get('hidden_training_search_by_skill');
                        $skill_name = $request->get('training_search_by_skill');
                        if ($skill_id && $skill_name) {
                            $search['id'] = $skill_id;
                            $search['name'] = $skill_name;
                            $skill = $skillRepository->findOneBy(['id' => $skill_id]);
                            $trainings = $trainingRepository->searchTrainingBySkill($skill);
                        }
                    break;
                default:
                    $trainings = false;
                    break;
            }
        }

        return $this->render('front/search/search_results.html.twig', ['trainings' => $trainings, 'search' => $search]);
    }
}
