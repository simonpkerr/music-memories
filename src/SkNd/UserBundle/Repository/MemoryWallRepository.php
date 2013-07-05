<?php

/*
 * Original code Copyright (c) 2012 Simon Kerr
 * @class MemoryWallRepository controls access to the db for controlling memory walls and associated activities
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SkNd\MediaBundle\Entity\Decade;

class MemoryWallRepository extends EntityRepository
{
    public function getPublicMemoryWalls(){
        $q = $this->createQueryBuilder('mw');
                
        $q = $this->getPublicMemoryWallsQuery($q);
        $q = $q->orderBy('mw.dateCreated', 'DESC')
               ->getQuery();
        
        return $q->getResult();
    }
    
    public function getMemoryWallBySlug($slug){
        return $this->createQueryBuilder('mw')
                ->where('mw.slug = :slug')
                ->setParameter('slug', $slug)
                ->getQuery()
                ->getOneOrNullResult();
    }
    
     public function getMemoryWallById($id){
        return $this->findOneBy(array('id' => $id));
    }
    
    /**
     * gets memory walls based on a specified decade
     * for use in making recommendations
     * @param Decade $decade 
     * @return array
     */
    public function getMemoryWallsByDecade(Decade $decade){
        $q = $this->createQueryBuilder('mw');
        $q = $this->getPublicMemoryWallsQuery($q);
        $q = $q->andWhere('mw.associatedDecade = :decadeId')
                ->setParameter('decadeId', $decade)
                ->orderBy('mw.lastUpdated', 'DESC')
                ->getQuery();
                
        return $q->getResult();
                 
    }
    
    private function getPublicMemoryWallsQuery($q){
        $q = $q->where('mw.isPublic = :public')
                ->setParameter('public', true);
        
        return $q;
    }

}