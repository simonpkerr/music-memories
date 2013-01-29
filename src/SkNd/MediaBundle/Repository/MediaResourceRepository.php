<?php

/*
 * Original code Copyright (c) 2012 Simon Kerr
 * MediaResourceRepository controls access to db and deletion of old cached records based on time stamp
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\MediaResource;

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
    
    public function getMediaResourceRecommendations(MediaResource $mr){
        $recommendations = array(
            'genericMatches'   => array(),
            'exactMatches'     => array(),
        );
        
        /*only find generic matches if the decade is not null (all decades) */
        $decade = !is_null($mr->getDecade()) ? $mr->getDecade() : null;
               
        //get media resources based most generic data but not including the selected item
        if(is_null($decade)){
            return $recommendations;
        }
        
        $params = array(
            'api'       => $mr->getAPI(),
            'mediaType' => $mr->getMediaType(),
            'genre'     => $mr->getGenre(),
            );
        
        $q = $this->createQueryBuilder('mr')
                ->where('mr.decade = :decade')
                ->andWhere('mr.id != :itemId')
                ->andWhere('mr.api = :api')
                ->orderBy('mr.selectedCount','DESC')
                ->addOrderBy('mr.viewCount', 'DESC')
                ->addOrderBy('mr.lastUpdated', 'DESC')
                ->setMaxResults(50)
                ->setParameter('decade', $decade)
                ->setParameter('itemId', $mr->getId())        
                ->setParameter('api', $params['api'])
                ->getQuery();

        //index by is not natively supported by querybuilder, so injecting the index clause
        $q  = $q->setDQL(str_replace('WHERE', 'INDEX BY mr.id WHERE', $q->getDQL()));
        $genericMatches = $q->getResult();
 
        $exactMatches = array();
        if(!is_null($params['genre'])){
            $exactMatches = array_filter($genericMatches, function($gm) use ($params){
                return $gm->getMediaType() == $params['mediaType']
                        && $gm->getGenre() == $params['genre'];
            });
        }
               
        //remove exact matches from the generic array and return the first 4 items
        $genericMatches = array_diff_key($genericMatches, $exactMatches);
        $genericMatches = array_slice($genericMatches, 0, 4); 
        $exactMatches = array_slice($exactMatches, 0, 4);
        
        if(count($genericMatches) == 0 && count($exactMatches) == 0)
            return $recommendations;
        
        return array(
            'genericMatches'   => $genericMatches,
            'exactMatches'     => $exactMatches,
        );
        
    }
    
}