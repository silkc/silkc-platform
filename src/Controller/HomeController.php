<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Training;
use App\Repository\UserRepository;
use App\Repository\SkillRepository;
use App\Repository\TrainingRepository;
use App\Repository\OccupationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

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

        return $this->render('front/home/search.html.twig');
    }

    /**
     * @Route("/search_results", name="search_results")
     */
    public function searchResults(Request $request, OccupationRepository $occupationRepository, TrainingRepository $trainingRepository, SkillRepository $skillRepository): Response
    {        
        $type_search = $request->get('type_search'); // Type de recherche (occupation ou skill)
        $trainings = []; // Listes des formations
        $search = []; // Parametres de recherche renvoyés à la vue
        
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

        return $this->render('front/search/index.html.twig', ['trainings' => $trainings, 'search' => $search]);
    }

    /**
     * @Route("/account", name="account")
     */
    public function account(): Response
    {
        return $this->render('front/account/index.html.twig');
    }

    /**
     * @Route("/institution", name="institutional")
     */
    public function institution(Request $request, TrainingRepository $trainingRepository): Response
    {

        $tab_active = false; // false, 1, 2 ou 3 (correspond aux onglets)
        $training_active = false; // ID du training actif

        if ($request->isMethod('post')) {
            if ($request->request->get('training_id')) { // Mise à jour
                $training_id = $request->request->get('training_id');
                $entityManager = $this->getDoctrine()->getManager();
                $training = $trainingRepository->find($training_id);
                if (!$training) {
                    throw $this->createNotFoundException(
                        'No training found for id '. $training_id
                    );
                }
            } else { // Ajout
                $training = new Training();
            }
            
            if ($training) {
                $training->setName($request->request->get('name'));
                $training->setLocation($request->request->get('location'));
                $training->setDuration($request->request->get('duration'));
                $training->setDescription($request->request->get('description'));
                $training->setPrice($request->request->get('price'));
                $training->setStartAt(null);
                $training->setEndAt(null);
                $training->setUrl($request->request->get('url'));
                //$training->setFile($request->request->get('file'));
            }
            
            $entityManager->persist($training);
            $entityManager->flush();
            $tab_active = 2;
            $training_active = $training->getId();
        }
        


        /*if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );
        }*/




        $trainings = $trainingRepository->findAll();
        return $this->render('front/institutional/index.html.twig', 
            [
                'trainings'       => $trainings, 
                'tab_active'      => $tab_active, 
                'training_active' => $training_active
            ]
        );
    }

    /**
     * @Route("/duplicate_training", name="duplicate_training")
     */
    public function duplicateTraining(Request $request, TrainingRepository $trainingRepository, SerializerInterface $serializer): Response
    {
        $trainingId = $request->get('training_id'); // id du training à dupliquer
        if (!$trainingId) return false;
        $training = $trainingRepository->findById($trainingId);
        $jsonContent = $serializer->serialize($training, 'json');
        return new JsonResponse($jsonContent);
    }
}
