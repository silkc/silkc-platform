<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Training;

class TrainingPersister implements DataPersisterInterface
{
    protected $_em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->_em = $em;
    }

    public function supports($data): bool
    {
        // Dans quel cas TrainingPersister est utilisé
        return $data instanceof Training;
    }

    public function persist($data)
    {
        $data->setCreatedAt(new \DateTime());
        $this->_em->persist($data);
        $this->_em->flush();
    }

    public function remove($data)
    {
        $this->_em->remove($data);
        $this->_em->flus($data);
    }
}