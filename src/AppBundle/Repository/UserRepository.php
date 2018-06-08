<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;


class UserRepository extends EntityRepository
{
    public function listadoUsuarios()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT u.loginUsername AS dni FROM AppBundle:User u WHERE u.studentGroup != false  ')
            ->getResult();
    }

}