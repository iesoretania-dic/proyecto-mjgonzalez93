<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Agreement;
use Doctrine\ORM\EntityRepository;


class AgreementRepository extends EntityRepository
{
    public function listadoAcuerdos()
    {
        return $this->createQueryBuilder('a')
            ->addSelect('w')
            ->addSelect('t')
            ->addSelect('c')
            ->addSelect('u')
            ->join('a.workTutor', 'w')
            ->join('a.educationalTutor', 't')
            ->join('a.workcenter', 'c')
            ->join('a.student', 'u')
            ->getQuery()
            ->getResult();
    }



}