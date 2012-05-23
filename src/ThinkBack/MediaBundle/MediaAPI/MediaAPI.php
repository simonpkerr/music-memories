<?php
namespace ThinkBack\MediaBundle\MediaAPI;
use Symfony\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use ThinkBack\MediaBundle\MediaAPI\IAPIStrategy;
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
    
    protected $response = null;
    protected $cachedListingsExist = false;
    
    /*
     * not sure how to implement debug_mode yet
     * so that none of the apis in the array call 
     * a live api
     * 
     * @description - gets the current run mode, passes the doctrine object 
     * and an array of api objects
     */
    public function __construct($debug_mode, Registry $doctrine, array $apis){
        $this->apis = $apis;
        $this->debugMode = $debug_mode;
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getEntityManager();
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
    public function getDetails(array $params, array $searchParams){
        $this->response = null;
        //look up the detail in the db to see if its cached
        
        //get the recommendations
        
        //look up the details from the api if not cached
        
        //store the recommendation in cache
        
        $this->response = $this->apiStrategy->getDetails($params, $searchParams);
        
        return $this->response;
    }
    
    /*
     * getListings calls the api of the current strategy
     * but first checks to see if the query is in the listings cache table along with results
     * @param params - contains the media,decade,genre,keywords,page used to find results
     * 
     */
    public function getListings(array $params){
        //$params = $this->removeDefaultValues($params);
                
        $this->response = null;
           
        $this->response = $this->getCachedListings($params);
        //look up the query from the db and return cached listings if available
        if($this->cachedListingsExist){
            return $this->response;
            
        }else{
            $this->response = $this->apiStrategy->getListings($params);
        }
        
        //get recommendations that exist only in the db
        
        
        //once results are retrieved insert into cache
        $this->cacheListings($this->response, $params);
        
        return $this->response;
    }
    
    //only results returned from the live api are cached
    public function cacheListings($response, $params){
        $cachedListing = new \ThinkBack\MediaBundle\Entity\MediaResourceListingsCache();
        $cachedListing->setAPI($this->apiStrategy);
        $cachedListing->setMediaType($mediaType)
        $this->em->persist($cachedListing);
        $this->em->flush();
    }
    
    public function getCachedListings($params){
        //look up the MediaResourceListingsCache with the params and the current apistrategy name   
        $xmlResponse = $this->em->getRepository('ThinkBackMediaBundle:MediaResourceListingsCache')->getCachedListings($params, $this->apiStrategy->API_NAME);
        
        if($xmlResponse != null){
            $this->cachedListingsExist = true;
            return @simplexml_load_string($xmlResponse[0]);
        }
        else{
            $this->cachedListingsExist = false;
            return null;
        }
        
        
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
        //todo
        //return $this->apiStrategy->getRecommendations($params, $recommendationType);
        
    }
    
    public function setRecommendation(array $params){
        //todo
    }
    
    //removes default values from the params array so that queries are executed correctly.
    private function removeDefaultValues(array $params){
        $params['decade'] = $params['decade'] == Decade::$default ? null : $params['decade'];
        $params['genre'] = $params['genre'] == Genre::$default ? null : $params['genre'];
        
    }
    
    
    
}
?>
