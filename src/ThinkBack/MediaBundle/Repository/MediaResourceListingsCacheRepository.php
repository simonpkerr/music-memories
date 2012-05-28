<?php

namespace ThinkBack\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ThinkBack\MediaBundle\Entity\Decade;
use ThinkBack\MediaBundle\Entity\Genre;
use ThinkBack\MediaBundle\Entity\MediaSelection;

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaController controls all aspects of connecting to and displaying media
 * @author Simon Kerr
 * @version 1.0
 */

class MediaResourceListingsCacheRepository extends EntityRepository
{
    /* gets cached listings based on search parameters and api type as well as timeStamp on record
     * records older than 24 hours must be removed
     * @params includes mediatype, optional decade, optional genre, optional keywords,
     * optional page
    */
    public function getCachedListings(MediaSelection $mediaSelection, $apiName){
  
        //initial selection parameters
        
        $q = $this->createQueryBuilder('cl')
                ->select('cl.xmlData, cl.dateCreated, cl.id')
                ->innerJoin('cl.api', 'a')
                ->where('cl.mediaType_id = :mediaTypeId')
                ->andWhere('a.apiName = :apiName')
                ->setParameters(array(
                    'mediaTypeId'        =>  $mediaSelection->getMediaTypes()->getId(),
                    'apiName'            =>  $apiName,
                   ));
         
        /*
         * if a specific decade was passed as a param, search for this
         * otherwise search for records with a null value for decade
         */
        if($mediaSelection->getDecades() != null){
            $q = $q->andWhere('cl.decade_id = :decadeId')
                    ->setParameter('decadeId', $mediaSelection->getDecades()->getId());
        }else {
            $q = $q->andWhere('cl.decade_id is null');
        }
        
        if($mediaSelection->getSelectedMediaGenres() != null){
            $q = $q->andWhere('cl.genre_id = :genreId')
                ->setParameter('genreId', $mediaSelection->getSelectedMediaGenres()->getId());
        }else{
            $q = $q->andWhere('cl.genre_id is null');
        }
        
        if($mediaSelection->getKeywords() != null){
            $q = $q->andWhere('cl.keywords = :keywords')
                ->setParameter('keywords', $mediaSelection->getKeywords());
        }else{
            $q = $q->andWhere('cl.keywords is null');
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
        else if($q['dateCreated'] < $this->getValidCreationTime()){
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
     
     
     private function getValidCreationTime(){
         $date = new \DateTime("now");
         $date = $date->sub(new \DateInterval('PT24H'))->format("Y-m-d H:i:s");

         return $date;
     }
     
     
}