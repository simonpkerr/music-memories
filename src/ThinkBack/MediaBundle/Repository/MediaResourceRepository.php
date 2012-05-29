<?php

namespace ThinkBack\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ThinkBack\MediaBundle\MediaAPI\Utilities;

/*
 * Original code Copyright (c) 2012 Simon Kerr
 * MediaResourceRepository controls access to db and deletion of old cached records based on time stamp
 * @author Simon Kerr
 * @version 1.0
 */

class MediaResourceRepository extends EntityRepository
{
    public function getMediaResourceById($itemId){
        $q = $this->createQueryBuilder('mr')
                ->select('mr')
                ->where('mr.id = :itemId')
                ->setParameter('itemId', $itemId)
                ->setMaxResults(1)
                ->getQuery();
        
        $mediaResource = $q->getOneOrNullResult();
        
        return $mediaResource;
        
    }
}