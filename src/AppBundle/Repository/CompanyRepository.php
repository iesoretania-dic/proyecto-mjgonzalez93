<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Company;
use Doctrine\ORM\EntityRepository;


class CompanyRepository extends EntityRepository
{
    public function listadoEmpresas()
    {
        return $this->createQueryBuilder('e')
            ->addSelect('u')
            ->join('e.manager', 'u')
            ->getQuery()
            ->getResult();
    }



}