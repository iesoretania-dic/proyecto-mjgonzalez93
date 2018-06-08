<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;


class ClassroomRepository extends EntityRepository
{
    public function obtencionID($nombre)
    {
        return $this->createQueryBuilder('c')
            ->where('c.name = :nombre')
            ->setParameter('nombre', $nombre)
            ->getQuery()
            ->getResult();
    }

}