<?php
namespace ThinkBack\MediaBundle\MediaAPI;
use Symfony\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;

class MediaAPI {
    public static $EXACT_RECOMMENDATION = 1;
    public static $AGE_RECOMMENDATION = 2;
    public static $GENERAL_RECOMMENDATION = 3;
    
    private $apiStrategy;
    private $apis;
    private $debugMode = false;
    private $doctrine;
    private $em;
    
    
    /*
     * not sure how to implement debug_mode yet
     * so that none of the apis in the array call 
     * a live api
     */
    public function __construct($debug_mode, Registry $doctrine, array $apis){
        $this->apis = $apis;
        $this->debugMode = $debug_mode;
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getEntityManager();
        $this->setAPIStrategy('amazonapi');
    }
    
    public function getAPIs(){
        return $this->apis;
    }
    
    /*
     * getDetails calls the api of the current strategy
     * but first gets recommendations from the db about that api
     * @param searchParams - contains the media,decade,genre,keywords,page used to find results
     * @param params - contains the relevant parameters to call the api. for amazon this is things
     * like Operation, Id. For youtube it contains things like keywords, decade, media etc
     */
    public function getDetails(array $params, array $searchParams){
        //look up the detail in the db to see if its cached
        
        //get the recommendations
        
        //look up the details from the api if not cached
        
        //store the recommendation in cache
        
        $response = $this->apiStrategy->getDetails($params, $searchParams);

        
        return $response;
    }
    
    /*
     * getListings calls the api of the current strategy
     * but first checks to see if the query is in the listings cache table along with results
     * @param searchParams - contains the media,decade,genre,keywords,page used to find results
     * 
     */
    public function getListings(array $params){
        $response = null;
        //checking and handling caching handled by MediaAPI
        
        if($this->cachedListingsExist($params)){
        //look up the query from the db and return cached listings if available
            
        }else{
            $response = $this->apiStrategy->getListings($params);
        }
        
        //get recommendations that exist only in the db
        
        
        //once results are retrieved update or insert into cache
        $this->cacheListings($response, $params);
        
        return $response;
    }
    
    public function setAPIStrategy($apiStrategyKey){
        if(isset($this->apis[$apiStrategyKey]))
            $this->apiStrategy = $this->apis[$apiStrategyKey];
        else
            throw new \RuntimeException("api key not found");
    }
    
    /*
     * for the current apistrategy, look up listings that
     * have been retrieved within the last 24 hours
     */
    public function cachedListingsExist($searchParams){
        return false;
    }
    
    public function cacheListings($response, $searchParams){
        //todo
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
    
    
    
    
}
?>
