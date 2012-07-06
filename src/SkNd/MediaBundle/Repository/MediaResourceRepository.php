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
    
    public function getMediaResourceRecommendations(MediaSelection $mediaSelection, $itemId){
        $genericMatches = array();
        $exactMatches = array();
        
        //get media resources based most generic data but not including the selected item
        $q = $this->createQueryBuilder('mr')
                ->where('mr.decade = :decade')
                ->andWhere('mr.id != :itemId')
                ->andWhere('mr.api = :api')
                ->orderBy('mr.selectedCount','DESC')
                ->addOrderBy('mr.viewCount', 'DESC')
                ->addOrderBy('mr.lastUpdated', 'DESC')
                ->setMaxResults(50)
                ->setParameter('decade', $mediaSelection->getDecade())
                ->setParameter('itemId', $itemId)        
                ->setParameter('api', $mediaSelection->getAPI())
                ->getQuery();
        
        //index by is not natively supported by querybuilder, so injecting the index clause
        $q  = $q->setDQL(str_replace('WHERE', 'INDEX BY mr.id WHERE', $q->getDQL()));
        $genericMatches = $q->getResult();
        
        //if genre and decade aren't specified, no point in getting exact matches
        //if(!(is_null($mediaSelection->getDecade()) || is_null($mediaSelection->getSelectedMediaGenre()))){
        
        //try to get exact matches based on the mediaSelection
        $exactMatches = array_filter($genericMatches, function($gm) use ($mediaSelection){
            return $gm->getMediaType() == $mediaSelection->getMediaType()
                    && $gm->getGenre() == $mediaSelection->getSelectedMediaGenre() 
                    && $gm->getAPI() == $mediaSelection->getAPI();
        });
        //}
        
        //remove exact matches from the generic array and return the first 4 items
        $genericMatches = array_diff_key($genericMatches, $exactMatches);
        $genericMatches = array_slice($genericMatches, 0, 4); 
        $exactMatches = array_slice($exactMatches, 0, 4);
        
        return array(
            'genericMatches'   => $genericMatches,
            'exactMatches'     => $exactMatches,
        );
        
    }
    
}