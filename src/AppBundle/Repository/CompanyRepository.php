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

    public function obtenerEmpresa($cif)
    {
        return $this->createQueryBuilder('e')
            ->where('e.code = :cif')
            ->setParameter('cif', $cif)
            ->getQuery()
            ->getSingleResult();
    }


}