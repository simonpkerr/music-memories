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
        $q = $this->createQueryBuilder('mr')
                ->where('mr.decade = :decade')
                ->orderBy('mr.selectedCount','DESC')
                ->addOrderBy('mr.viewCount', 'DESC')
                ->addOrderBy('mr.lastUpdated', 'DESC')
                ->setMaxResults(50)
                ->setParameter('decade', $mediaSelection->getDecade());

        $genericMatches = $q->getQuery()->getResult();
        
        //try to get exact matches based on the mediaSelection
        $exactMatches = array_filter($genericMatches, function($gm) use ($mediaSelection){
            return $gm['mediaType'] == $mediaSelection->getMediaType() && $gm['genre'] == $mediaSelection->getSelectedMediaGenre() && $gm['api'] == $mediaSelection->getAPI();
        });
        
        //remove exact matches from the generic array and return the first 4 items
        $genericMatches = array_slice(array_diff($genericMatches, $exactMatches), 0, 3); 
        $exactMatches = array_slice($exactMatches, 0, 3);
        
        return array(
            'genericMatches'   => $genericMatches,
            'exactMatches'     => $exactMatches,
        );
        
    }
    
}