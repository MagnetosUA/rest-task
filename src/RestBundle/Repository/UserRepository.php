<?php

namespace RestBundle\Repository;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{
    public function getUserByLogin($login)
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.login = :login')
            ->setParameter('login', $login)
            ->getQuery();

        return $query->getResult();
    }
}
