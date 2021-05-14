<?php

namespace App\Controller;

use App\Entity\UserOccupation;
use App\Repository\OccupationRepository;
use App\Repository\TrainingRepository;
use App\Repository\UserOccupationRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", name="api_")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/get_occupation", name="get_occupation", methods={"GET"})
     */
    public function get_occupation(TrainingRepository $trainingRepository)
    {
        $training = $trainingRepository->findOneBy(['id' => 1]);

        $skills = $training->getToAcquireSkills();

        foreach ($skills as $skill) {
            dump($skill->getSkill());
        }
        die();
    }

    /**
     * @Route("/user_occupation", name="user_occupation", methods={"POST"})
     */
    public function user_occupation(Request $request, OccupationRepository $occupationRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $params = $request->request->all();
        if (
            !$user ||
            !is_array($params) ||
            !array_key_exists('currentOccupations', $params) ||
            !array_key_exists('desiredOccupations', $params) ||
            !array_key_exists('previousOccupations', $params)
        )
            return new JsonResponse(['message' => 'Missing parameter'], Response::HTTP_BAD_REQUEST);

        $previousIds = (is_array($params['previousOccupations'])) ? array_filter($params['previousOccupations'], 'is_numeric'): null;
        $currentIds = (is_array($params['currentOccupations'])) ? array_filter($params['currentOccupations'], 'is_numeric') : null;
        $desiredIds = (is_array($params['desiredOccupations'])) ? array_filter($params['desiredOccupations'], 'is_numeric') : null;

        $previousOccupations = new ArrayCollection();
        $currentOccupations = new ArrayCollection();
        $desiredOccupations = new ArrayCollection();
        $userOccupations = $user->getUserOccupations();
        $newPreviousOccupations = new ArrayCollection($occupationRepository->findBy(['id' => $previousIds]));
        $newCurrentOccupations = new ArrayCollection($occupationRepository->findBy(['id' => $currentIds]));
        $newDesiredOccupations = new ArrayCollection($occupationRepository->findBy(['id' => $desiredIds]));

        foreach ($userOccupations as $userOccupation) {
            if ($userOccupation->getIsCurrent() === true) {
                $currentOccupations->add($userOccupation->getOccupation());
                if (!$newCurrentOccupations->contains($userOccupation->getOccupation()))
                    $user->removeUserOccupation($userOccupation);
            }
            if ($userOccupation->getIsPrevious() === true) {
                $previousOccupations->add($userOccupation->getOccupation());
                if (!$newPreviousOccupations->contains($userOccupation->getOccupation()))
                    $user->removeUserOccupation($userOccupation);
            }
            if ($userOccupation->getIsDesired() === true) {
                $desiredOccupations->add($userOccupation->getOccupation());
                if (!$newDesiredOccupations->contains($userOccupation->getOccupation()))
                    $user->removeUserOccupation($userOccupation);
            }
        }

        foreach ($newPreviousOccupations as $occupation) {
            if (!$previousOccupations->contains($occupation)) {
                $userOccupation = new UserOccupation();
                $userOccupation->setUser($user);
                $userOccupation->setOccupation($occupation);
                $userOccupation->setIsPrevious(true);
                $user->addUserOccupation($userOccupation);
                $em->persist($userOccupation);
            }
        }
        foreach ($newCurrentOccupations as $occupation) {
            if (!$currentOccupations->contains($occupation)) {
                $userOccupation = new UserOccupation();
                $userOccupation->setUser($user);
                $userOccupation->setOccupation($occupation);
                $userOccupation->setIsCurrent(true);
                $user->addUserOccupation($userOccupation);
                $em->persist($userOccupation);
            }
        }
        foreach ($newDesiredOccupations as $occupation) {
            if (!$desiredOccupations->contains($occupation)) {
                $userOccupation = new UserOccupation();
                $userOccupation->setUser($user);
                $userOccupation->setOccupation($occupation);
                $userOccupation->setIsDesired(true);
                $user->addUserOccupation($userOccupation);
                $em->persist($userOccupation);
            }
        }

        $em->persist($user);
        $em->flush();

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/user_training", name="user_training", methods={"POST"})
     */
    public function user_training(Request $request, TrainingRepository $trainingRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $trainings_ids = $request->request->get('trainings');
        if (!$user || !$trainings_ids || !is_array($trainings_ids))
            return new JsonResponse(['message' => 'Missing parameter'], Response::HTTP_BAD_REQUEST);

        $result = $trainingRepository->findBy(['id' => array_filter($trainings_ids, 'is_numeric')]);
        $trainings = ($result) ? new ArrayCollection($result) : null;
        $userTrainings = $user->getTrainings();

        if ($trainings) {
            foreach ($trainings as $training) {
                if (!$userTrainings || !$userTrainings->contains($training))
                    $user->addTraining($training);
            }
        }

        foreach ($userTrainings as $userTraining) {
            if (!$trainings->contains($userTraining)) {
                $user->removeTraining($userTraining);
            }
        }

        $em->persist($user);
        $em->flush();

        return $this->json(['result' => true], 200, ['Access-Control-Allow-Origin' => '*']);
    }
}
