<?php

/**
 * @abstract ProcessBatchStrategy
 * @copyright Simon Kerr 2012
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\MediaBundle\MediaAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use Doctrine\ORM\EntityManager;
use SkNd\MediaBundle\MediaAPI\IAPIStrategy;
use SkNd\MediaBundle\MediaAPI\MediaDetails;

class ProcessListingsStrategy implements IProcessMediaStrategy {
    protected $apiStrategy;
    protected $em;
    protected $mediaSelection;
    protected $listings;
       
    /**
     * @param array $params includes -
     * @param EntityManager $em, 
     * @param array $apiStrategy,
     * @param MediaSelection $mediaSelection 
     */
    public function __construct(array $params){
        $this->em = $params['em'];
        $this->mediaSelection = $params['mediaSelection'];
        $this->apiStrategy = $params['apiStrategy'];
        $this->listings = null;
    }
    
    public function getAPIData(){
        return $this->apiStrategy;
    }
    
    public function getMedia(){ 
        if(is_null($this->listings))
            throw new \RuntimeException("listings are null");
            
        return $this->listings;
    }

    public function processMedia(){
        $this->listings = null;
           
        $this->listings = $this->getCachedListings();
        
        //look up the query from the db and return cached listings if available
        if(is_null($this->listings)){
            $this->listings = $this->apiStrategy->getListings($this->mediaSelection);
            
            //once results are retrieved insert into cache
            //$this->cacheListings($this->listings);
        } 
        
        $recommendations = $this->getRecommendations($recType);
        return array(
            'response'          => $this->response,
            'recommendations'   => $recommendations,
        );
    }
    
    //from a batch operation, take the xml data and resources and re-cache them
    public function cacheMedia(){ 
    //public function cacheMediaResourceBatch(SimpleXMLElement $xmlData, array $mediaResources, $immediateFlush = true){
        /* all elements in the arrays are either existing mediaresources 
         * or the details mediaresource without cache
         */
        
        foreach($this->apiReponses as $apiResponse){
            $api = $apiResponse['api'];
            $mediaResources = $apiResponse['mediaResources'];
            
            foreach($apiResponse['xmlData'] as $itemXml){
                $id = $api->getIdFromXML($itemXml);
                //if media resource exists, re-cache the data
                if(isset($mediaResources[$id])){
                    $mr = $mediaResources[$id];
                    $cachedResource = new MediaResourceCache();
                    $cachedResource->setId($id);
                    $cachedResource->setImageUrl($api->getImageUrlFromXML($itemXml));
                    $cachedResource->setTitle($api->getItemTitleFromXML($itemXml));
                    $cachedResource->setXmlData($api->getXML($itemXml));
                    $cachedResource->setDateCreated(new \DateTime("now"));
                    try{
                        $mr->setMediaResourceCache($cachedResource);
                        $this->persistMergeMediaResource($mr);
                    } catch(\Exception $ex){
                        throw $ex;
                    }
                } 
                //IS THIS NEEDED?
                ////else{
                    //otherwise create a new media resource and cache it
                    //$this->mediaResource = null;
                    
                    //HOW TO CONNECT CACHE MEDIA RESOURCE WHICH EXISTS IN PROCESSDETAILSSTRATEGY
                   // $this->cacheMediaResource($itemXml, $id, false);                
                //}
            }
        }
        
        
        
        $this->em->flush();
    
    }
    
    public function persistMergeMediaResource(MediaResource $mediaResource){
        if($this->em->contains($mediaResource))
            $this->em->merge($mediaResource);
        else
            $this->em->persist($mediaResource);
    }

    
}


?>
