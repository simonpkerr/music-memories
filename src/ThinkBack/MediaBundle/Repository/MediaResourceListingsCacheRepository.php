<?php

namespace ThinkBack\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ThinkBack\MediaBundle\Entity\Decade;
use ThinkBack\MediaBundle\Entity\Genre;

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
    public function getCachedListings(array $params, $apiName){
  
        //initial selection parameters
        
        $q = $this->createQueryBuilder('cl')
                ->select('cl.xmlData, cl.dateCreated, cl.id')
                ->innerJoin('cl.mediaType', 'm')
                ->innerJoin('cl.api', 'a')
                ->where('m.slug = :mediaSlug')
                ->andWhere('a.apiName = :apiName')
                
                //->andWhere('cl.dateCreated >= :validCreationTime')
                ->setParameters(array(
                    'mediaSlug'         =>  $params['media'],
                    'apiName'           =>  $apiName,
                    //'validCreationTime' =>  $this->getValidCreationTime()
                    
                   ));
         
        /*
         * if a specific decade was passed as a param, search for this
         * otherwise search for records with a null value for decade
         */
        if($params['decade'] != Decade::$default){
            $q = $q->innerJoin('cl.decade', 'd')
                    ->andWhere('d.slug = :decadeSlug')
                    ->setParameter('decadeSlug', $params['decade']);
        }else {
            $q = $q->andWhere('cl.decade_id is null');
        }
        
        if($params['genre'] != Genre::$default){
            $q = $q->innerJoin('cl.genre', 'g')
                ->andWhere('g.slug = :genreSlug')
                ->setParameter('genreSlug', $params['genre']);
        }else{
            $q = $q->andWhere('cl.genre_id is null');
        }
        
        if($params['keywords'] != '-'){
            $q = $q->andWhere('cl.keywords = :keywords')
                ->setParameter('keywords', $params['keywords']);
        }else{
            $q = $q->andWhere('cl.keywords is null');
        }
        
        if($params['page'] != 1){
            $q = $q->andWhere('cl.page = :page')
                ->setParameter('page', $params['page']);
        }else{
            $q = $q->andWhere('cl.page is null');
        }

        $q = $q->getQuery()->getOneOrNullResult();
        
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
     
     //if listings were retrieved from a live api, cache them with a timestamp
     /*public function setCachedListings($xmlData, array $params, $apiName){
         $q = $this->createQueryBuilder('cl')
                 ->
     }*/
     
     private function getValidCreationTime(){
         $date = new \DateTime("now");
         $date = $date->sub(new \DateInterval('PT24H'))->format("Y-m-d H:i:s");

         return $date;
     }
     
     
}