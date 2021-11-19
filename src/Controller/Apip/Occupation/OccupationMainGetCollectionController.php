<?php

namespace App\Controller\Apip\Occupation;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Repository\OccupationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use ApiPlatform\Core\Attribute\AsController;
use Symfony\Component\Intl\Locale;

class OccupationMainGetCollectionController extends AbstractController
{
    private $occupationRepository;

    public function __construct(OccupationRepository $occupationRepository)
    {
        $this->occupationRepository = $occupationRepository;
    }

    public function __invoke(Request $request, string $locale)
    {
        Locale::setDefaultFallback($locale);
        return $this->occupationRepository->findAllByLocale($locale);
    }
}