<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;


class UserRepository extends EntityRepository
{
    public function listadoDNIAlumnos()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT u.reference AS dni FROM AppBundle:User u WHERE u.studentGroup != false  ')
            ->getResult();
    }

    public function listadoAlumnos()
    {
        return $this->createQueryBuilder('a')
            ->where('a.studentGroup != :usuario')
            ->setParameter('usuario', false)
            ->getQuery()
            ->getResult();
    }

}