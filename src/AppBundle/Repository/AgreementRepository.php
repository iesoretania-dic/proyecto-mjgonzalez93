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

    public function listadoAcuerdosManager($id)
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
            ->where('a.workTutor = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    public function listadoAcuerdosTutor($id)
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
            ->where('a.educationalTutor = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    public function listadoAcuerdosEmpresa($id)
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
            ->where('a.workcenter = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }



}