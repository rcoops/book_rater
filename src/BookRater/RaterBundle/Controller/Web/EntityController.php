<?php


namespace BookRater\RaterBundle\Controller\Web;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\RouterInterface;

class EntityController extends Controller
{

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * EntityController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function paginate($query, $request)
    {
        return $this->get('knp_paginator')->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
    }

}
