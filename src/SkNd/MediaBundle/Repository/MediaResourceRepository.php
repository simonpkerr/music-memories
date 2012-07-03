<?php

namespace SkNd\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\Entity\MediaSelection;
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
    
    public function getMediaResourceRecommendations(MediaSelection $mediaSelection){
        //get media resources based most generic data
        if($mediaSelection->getDecade() != null){
            $q = $this->createQueryBuilder('mr')
                    ->where('mr.decade = :decade')
                    ->setParameter('decade', $mediaSelection->getDecade());
        } else {
            $q = $this->createQueryBuilder('mr')
                    ->where('mr.mediaType = :mediaType')
                    ->setParameter('mediaType', $mediaSelection->getMediaType());
        }
        
    }
    
}