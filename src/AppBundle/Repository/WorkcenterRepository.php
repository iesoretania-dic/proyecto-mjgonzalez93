<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Workcenter;
use Doctrine\ORM\EntityRepository;


class WorkcenterRepository extends EntityRepository
{
    public function obtenerCentros($empresa)
    {
        return $this->createQueryBuilder('w')
            ->where('w.company = :empresa')
            ->setParameter('empresa', $empresa)
            ->getQuery()
            ->getSingleResult();
    }

    public function listadoCentros()
    {
        return $this->createQueryBuilder('w')
            ->getQuery()
            ->getResult();
    }

}