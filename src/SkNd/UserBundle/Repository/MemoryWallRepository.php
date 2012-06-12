<?php

namespace SkNd\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SkNd\UserBundle\Entity\MemoryWall;

/*
 * Original code Copyright (c) 2012 Simon Kerr
 * @class MemoryWallRepository controls access to the db for controlling memory walls and associated activities
 * @author Simon Kerr
 * @version 1.0
 */

class MemoryWallRepository extends EntityRepository
{
    public function getPublicMemoryWalls(){
        return $this->createQueryBuilder('mw')
                ->where('mw.isPublic = :public')
                ->orderBy('mw.dateCreated', 'DESC')
                ->setParameter('public', true)
                ->getQuery()
                ->getResult();
    }
    
    public function getMemoryWallBySlug($slug){
        return $this->createQueryBuilder('mw')
                ->where('mw.slug = :slug')
                ->setParameter('slug', $slug)
                ->getQuery()
                ->getOneOrNullResult();
    }
    

}