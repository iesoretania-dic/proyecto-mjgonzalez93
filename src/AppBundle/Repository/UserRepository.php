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

    public function usuarioPassword($id)
    {
        return $this->createQueryBuilder('u')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }

    public function listadoDNIManagers()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT u.reference AS dni FROM AppBundle:User u WHERE u.financialManager != false  ')
            ->getResult();
    }

    public function listadoManagers()
    {
        return $this->createQueryBuilder('m')
            ->where('m.financialManager = :manager')
            ->setParameter('manager', true)
            ->getQuery()
            ->getResult();
    }

    public function listadoTutores($id)
    {
        return $this->createQueryBuilder('u')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }

}