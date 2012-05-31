<?php
namespace SkNd\MediaBundle\MediaAPI;
use Symfony\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use SkNd\MediaBundle\MediaAPI\IAPIStrategy;
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\MediaResource;
use SkNd\MediaBundle\Entity\MediaResourceCache;

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaAPI controls access to the various apis and their operations,
 * checking for cached versions of details or listings
 * @author Simon Kerr
 * @version 1.0
 */

class MediaAPI {
    public static $EXACT_RECOMMENDATION = 1;
    public static $AGE_RECOMMENDATION = 2;
    public static $GENERAL_RECOMMENDATION = 3;
    
    protected $apiStrategy;
    protected $apis;
    protected $debugMode = false;
    protected $doctrine;
    protected $em;
    protected $mediaSelection;
    
    protected $response = null;
    protected $cachedDataExist = false;
    
    //used when details are being retrieved, gets the mediaResource and cached object if available
    protected $mediaResource;
    
    /*
     * not sure how to implement debug_mode yet
     * so that none of the apis in the array call 
     * a live api
     * 
     * @description - gets the current run mode, passes the doctrine object 
     * and an array of api objects
     */
    public function __construct($debug_mode, EntityManager $em, array $apis){
        $this->apis = $apis;
        $this->debugMode = $debug_mode;
        $this->em = $em;
        
        //this needs changing to accomodate different APIs
        $this->setAPIStrategy('amazonapi');
    }
    
    public function getAPIs(){
        return $this->apis;
    }
    
    public function setAPIStrategy($apiStrategyKey){
        if(array_key_exists($apiStrategyKey, $this->apis))
            $this->apiStrategy = $this->apis[$apiStrategyKey];
        else
            throw new \RuntimeException("api key not found");
    }
    
    /*
     * getDetails calls the api of the current strategy
     * but first gets recommendations from the db about that api
     * @param searchParams - contains the media,decade,genre,keywords,page used to find results
     * @param params - contains the relevant parameters to call the api. for amazon this is things
     * like Operation, Id. For youtube it contains things like keywords, decade, media etc
     */
    public function getDetails(array $params, MediaSelection $mediaSelection){
        $this->mediaSelection = $mediaSelection;
        $this->response = null;
        
        //look up the mediaResource in the db and fetch associated cached object
        $this->mediaResource = $this->getMediaResource($params['ItemId']);
        
        $this->cachedDataExist = ($this->mediaResource != null && $this->mediaResource->getMediaResourceCache() != null) ? true : false;
        //look up the details from the api if not cached
        if(!$this->cachedDataExist)
            $this->response = $this->apiStrategy->getDetails($params);
        else
            $this->response = @simplexml_load_string($this->mediaResource->getMediaResourceCache()->getXmlData());
        
        //cache the data
        $this->cacheMediaResource($this->response, $params['ItemId']);
        
        //get the recommendations
        //TODO       
                
        return $this->response;
    }
    
    /*
     * getListings calls the api of the current strategy
     * but first checks to see if the query is in the listings cache table along with results
     * @param params - contains the media,decade,genre,keywords,page used to find results
     * 
     */
    public function getListings(MediaSelection $mediaSelection){
        $this->mediaSelection = $mediaSelection;
        $this->response = null;
           
        $this->response = $this->getCachedListings();
        $this->cachedDataExist = $this->response != null ? true : false;
        
        //look up the query from the db and return cached listings if available
        if(!$this->cachedDataExist){
            $this->response = $this->apiStrategy->getListings($this->mediaSelection);
            //once results are retrieved insert into cache
            $this->cacheListings($this->response);
        }
        
        //get recommendations of media resources for the same parameters that exist only in the db
        //TODO
        
        return $this->response;
    }
    
    //only results returned from the live api are cached
    public function cacheListings(\SimpleXMLElement $response){
        $cachedListing = new \SkNd\MediaBundle\Entity\MediaResourceListingsCache();
        $cachedListing->setAPI($this->em->getRepository('SkNdMediaBundle:API')->getAPIByName($this->apiStrategy->API_NAME));
        $cachedListing->setMediaType($this->mediaSelection->getMediaTypes());
        $cachedListing->setDecade($this->mediaSelection->getDecades());
        $cachedListing->setGenre($this->mediaSelection->getSelectedMediaGenres());
        $cachedListing->setKeywords($this->mediaSelection->getKeywords());
        $cachedListing->setPage($this->mediaSelection->getPage() != 1 ? $this->mediaSelection->getPage() : null);
        $cachedListing->setXmlData($response->asXML());        
        
        $this->em->persist($cachedListing);
        $this->em->flush();
    }
    
    public function getCachedListings(){
        //look up the MediaResourceListingsCache with the params and the current apistrategy name   
        $xmlResponse = $this->em->getRepository('SkNdMediaBundle:MediaResourceListingsCache')->getCachedListings($this->mediaSelection, $this->apiStrategy->API_NAME);
        
        if($xmlResponse != null){
            return @simplexml_load_string($xmlResponse);
        }
        else{
            return null;
        }
    }
    
    //get an individual media resource based on item id and retrieve or delete associated cached resource
    public function getMediaResource($itemId){
        $this->mediaResource = $this->em->getRepository('SkNdMediaBundle:MediaResource')->getMediaResourceById($itemId);
        if($this->mediaResource != null && $this->mediaResource->getMediaResourceCache() != null){
            //if a cached resource exists and is older than 24 hours, delete it
            $dateCreated = $this->mediaResource->getMediaResourceCache()->getDateCreated();
            if($dateCreated->format("Y-m-d H:i:s") < Utilities::getValidCreationTime()){
                $this->mediaResource->deleteMediaResourceCache();
                $this->em->flush();
            }
        }
        
        return $this->mediaResource;
    }
    
    //once retrieved, if applicable, cache the resource
    public function cacheMediaResource(\SimpleXMLElement $xmlData, $itemId){
        if($this->mediaResource == null){
            //create a mediaresource
            $this->mediaResource = new MediaResource();
            $this->mediaResource->setId($itemId);
            $this->mediaResource->setAPI($this->em->getRepository('SkNdMediaBundle:API')->getAPIByName($this->apiStrategy->API_NAME));
            $this->mediaResource->setDecade($this->mediaSelection->getDecades());
            $this->mediaResource->setMediaType($this->mediaSelection->getMediaTypes());
            $this->mediaResource->setGenre($this->mediaSelection->getSelectedMediaGenres());
        }
            
        //if cached listings not exists create a new MediaResourceCache object and update the mediaResource    
        if($this->mediaResource->getMediaResourceCache() == null){
            $cachedResource = new MediaResourceCache();
            $cachedResource->setId($this->mediaResource->getId());
            $cachedResource->setImageUrl($this->apiStrategy->getImageUrlFromXML($xmlData));
            $cachedResource->setTitle($this->apiStrategy->getItemTitleFromXML($xmlData));
            $cachedResource->setXmlData($xmlData->asXML());
            $this->mediaResource->setMediaResourceCache($cachedResource);
        }

        $this->mediaResource->incrementViewCount();
        
        $this->em->persist($this->mediaResource);
        $this->em->flush();
    }
    
    
    /*
     * will receive an array of parameters specifying criteria to query the db
     * along with the type of recommendations to find.
     * if doing an exact search, media, decade and genre are passed
     * if doing an age search, the users date of birth is passed
     * db is queried and a list of ids are returned which may or may not
     * have associated objects if they exist in the cache table
     * 
     * this is based on the current apistrategy
     */
    public function getRecommendations(array $params, $recommendationType = 1){
        //TODO
        //return $this->apiStrategy->getRecommendations($params, $recommendationType);
        
    }
    
    public function setRecommendation(array $params){
        //TODO
    }
    
    //removes default values from the params array so that queries are executed correctly.
    private function removeDefaultValues(array $params){
        $params['decade'] = $params['decade'] == Decade::$default ? null : $params['decade'];
        $params['genre'] = $params['genre'] == Genre::$default ? null : $params['genre'];
        
    }
    
    
    
}
?>
