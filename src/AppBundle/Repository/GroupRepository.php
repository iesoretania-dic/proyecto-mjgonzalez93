<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Group;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;


class GroupRepository extends EntityRepository
{
    public function obtencionGrupo($id)
    {
        try {
            return $this->createQueryBuilder('g')
                ->where('g.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
        } catch (NonUniqueResultException $e) {
        }
    }

}