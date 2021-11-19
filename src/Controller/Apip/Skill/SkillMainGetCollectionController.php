<?php

namespace App\Controller\Apip\Skill;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Repository\SkillRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use ApiPlatform\Core\Attribute\AsController;
use Symfony\Component\Intl\Locale;

class SkillMainGetCollectionController extends AbstractController
{
    private $skillRepository;

    public function __construct(SkillRepository $skillRepository)
    {
        $this->skillRepository = $skillRepository;
    }

    public function __invoke(Request $request, string $locale)
    {
        Locale::setDefaultFallback($locale);
        return $this->skillRepository->findAllByLocale($locale);
    }
}