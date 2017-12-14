<?php

namespace BookRater\RaterBundle\Repository;

use \Doctrine\ORM\EntityRepository;

/**
 * BookRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BookRepository extends EntityRepository
{

    public function findAllOrderedByNameQB()
    {
        return $this->createQueryBuilder('book')
            ->orderBy('book.title');
    }
}
