<?php

namespace BookRater\RaterBundle\Repository;

use \Doctrine\ORM\EntityRepository;

/**
 * AuthorRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AuthorRepository extends EntityRepository
{

    public function findAllOrderedByName()
    {
        return $this->createQueryBuilder('author')
            ->addOrderBy('author.lastName')
            ->addOrderBy('author.firstName')
            ->addOrderBy('author.initial');
    }

}
