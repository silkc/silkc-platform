<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Type\InstitutionalTraining;

class InstitutionalController extends AbstractController
{
    /**
     * @Route("/institutional", name="institutional")
     */
    public function index(): Response
    {
        return $this->render('front/institutional/index.html.twig');
    }

    public function generateForm()
    {

    }
}
