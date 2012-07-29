<?php

namespace SkNd\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SkNd\MediaBundle\Entity\Decade;
use SkNd\MediaBundle\Entity\Genre;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\MediaAPI\IAPIStrategy;
/**
 * Original code Copyright (c) 2011 Simon Kerr
 * controls access and retrieval of cached listings based on media selection
 * @author Simon Kerr
 * @version 1.0
 **/

class MediaResourceListingsCacheRepository extends EntityRepository
{
    /* gets cached listings based on search parameters and api type as well as timeStamp on record
     * records older than 24 hours must be removed
     * @params includes mediatype, optional decade, optional genre, optional keywords,
     * optional page
    */
    public function getCachedListings(MediaSelection $mediaSelection, IAPIStrategy $api){
  
        $q = $this->createQueryBuilder('cl')
                ->select('cl.xmlData, cl.dateCreated, cl.id')
                ->where('cl.mediaType = :mediaType')
                ->andWhere('cl.api = :api')
                ->setParameters(array(
                    'mediaType'    =>  $mediaSelection->getMediaType(),
                    'api'          =>  $mediaSelection->getAPI(),
                   ));
         
        /*
         * if a specific decade was passed as a param, search for this
         * otherwise search for records with a null value for decade
         */
        if($mediaSelection->getDecade() != null){
            $q = $q->andWhere('cl.decade = :decade')
                    ->setParameter('decade', $mediaSelection->getDecade());
        }else {
            $q = $q->andWhere('cl.decade is null');
        }
        
        if($mediaSelection->getSelectedMediaGenre() != null){
            $q = $q->andWhere('cl.genre = :genre')
                ->setParameter('genre', $mediaSelection->getSelectedMediaGenre());
        }else{
            $q = $q->andWhere('cl.genre is null');
        }
        
        if($mediaSelection->getKeywords() != null){
            $q = $q->andWhere('cl.keywords = :keywords')
                ->setParameter('keywords', $mediaSelection->getKeywords());
        }else{
            $q = $q->andWhere('cl.keywords is null');
        }
        
        if($mediaSelection->getComputedKeywords() != null){
            $q = $q->andWhere('cl.computedKeywords = :computedKeywords')
                ->setParameter('computedKeywords', $mediaSelection->getComputedKeywords());
        }else{
            $q = $q->andWhere('cl.computedKeywords is null');
        }
        
        if($mediaSelection->getPage() != 1 && $mediaSelection->getPage() != null){
            $q = $q->andWhere('cl.page = :page')
                ->setParameter('page', $mediaSelection->getPage());
        }else{
            $q = $q->andWhere('cl.page is null');
        }
        
        $q = $q->getQuery()->setMaxResults(1)->getOneOrNullResult();
        
        if($q == null)
            return null;
        else if($q['dateCreated'] < $api->getValidCreationTime()){
            //delete the entry since the timestamp is out of date
            $this->createQueryBuilder('cl')
                    ->delete()
                    ->where('cl.id = :id')
                    ->setParameter('id', $q['id'])
                    ->getQuery()
                    ->execute();
            return null;
        }
        else
            return $q['xmlData'];

     }
     
}