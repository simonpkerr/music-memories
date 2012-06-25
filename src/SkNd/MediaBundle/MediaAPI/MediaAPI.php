<?php
namespace SkNd\MediaBundle\MediaAPI;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Session;
use Doctrine\ORM\EntityManager;
use SkNd\MediaBundle\MediaAPI\IAPIStrategy;
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\MediaType;
use SkNd\MediaBundle\Entity\Decade;
use SkNd\MediaBundle\Entity\Genre;
use SkNd\MediaBundle\Entity\MediaResource;
use SkNd\MediaBundle\Entity\MediaResourceCache;
use SkNd\UserBundle\Entity\MemoryWall;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use \RuntimeException;

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
    
    protected $session;
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
    public function __construct($debug_mode, EntityManager $em, Session $session, array $apis){
        $this->apis = $apis;
        $this->debugMode = $debug_mode;
        $this->em = $em;
        $this->session = $session;
        
        //this needs changing to accomodate different APIs
        $this->setAPIStrategy('amazonapi');
         ////$this->em->getRepository('SkNdMediaBundle:API')->getDefaultname());
        
        //if debug mode is true, set the api's to dummy objects
    }
    
    public function getEntityManager(){
        return $this->em;
    }
    
    public function setEntityManager(EntityManager $em){
        $this->em = $em;
    }
    
    public function getAPIs(){
        return $this->apis;
    }
    
    public function setSession(Session $session){
        $this->session = $session;
    }
    
    public function getSession(){
        return $this->session;
    }
    
    public function setAPIStrategy($apiStrategyKey){
        if(array_key_exists($apiStrategyKey, $this->apis))
            $this->apiStrategy = $this->apis[$apiStrategyKey];
        else
            throw new RuntimeException("api key not found");
    }
    
    public function getCurrentAPI(){
        return $this->apiStrategy;
    }
    
    //for testing purposes, allow injection of apis
    public function setAPIs(array $apis){
        $this->apis = $apis;
    }
    
    /*
     * check session, return mediaSelection if not null
     * else create new media selection and return
     * $params - contains optional media, decade, genre, keywords, page
     * if params exist, they should override the session values if different
     */
    public function getMediaSelection(array $params = null){
        $mediaTypeSlug = null;
        $decadeSlug = null;
        $genreSlug = null;
        $keywords = null;
        $computedKeywords = null;
        $page = 1;
        
        //try getting the media selection from the session
        if($this->mediaSelection == null)
            $this->mediaSelection = $this->session->get('mediaSelection') != null ? $this->session->get('mediaSelection') : new MediaSelection();
        
        $mediaType = $this->mediaSelection->getMediaTypes();
        $decade = $this->mediaSelection->getDecades();
        $genre = $this->mediaSelection->getSelectedMediaGenres();
        
        //if params passed, update the mediaSelection
        if($params != null){
            $mediaTypeSlug = isset($params['media']) ? $params['media'] : MediaType::$default;
            $decadeSlug = isset($params['decade']) ? $params['decade'] : Decade::$default;
            $genreSlug = isset($params['genre']) ? $params['genre'] : Genre::$default;
            $keywords = isset($params['keywords']) ? $params['keywords'] != '-' ? $params['keywords'] : null : null;
            $computedKeywords = isset($params['computedKeywords']) ? $params['computedKeywords'] : null;
            $page = isset($params['page']) ? $params['page'] : $page;
        
            //only update the mediaSelection if different
            if($this->mediaSelection->getMediaTypes() == null || $mediaTypeSlug != $this->mediaSelection->getMediaTypes()->getSlug()){
                $mediaType = $this->em->getRepository('SkNdMediaBundle:MediaType')->getMediaTypeBySlug($mediaTypeSlug);
                if($mediaType == null)
                    throw new NotFoundHttpException("There was a problem with that address");
            } 
            
            //if decade is not default decade and is not the same as existing decade
            if($decadeSlug != Decade::$default){
                if($this->mediaSelection->getDecades() == null || $decadeSlug != $this->mediaSelection->getDecades()->getSlug()){
                    $decade = $this->em->getRepository('SkNdMediaBundle:Decade')->getDecadeBySlug($decadeSlug);
                    if($decade == null)
                        throw new NotFoundHttpException ("There was a problem with that address");
                } 

            }else{
                //if the entity exists already, set to null so defaults to all-decades
                $this->mediaSelection->setDecades(null);
            }

            if($genreSlug != Genre::$default){
                //if the genre is different or the media type is different, reset the genre
                if($this->mediaSelection->getSelectedMediaGenres() == null || $genreSlug != $this->mediaSelection->getSelectedMediaGenres()->getSlug() || $mediaTypeSlug != $this->mediaSelection->getMediaTypes()->getSlug()){
                    try{
                        $genre = $this->em->getRepository('SkNdMediaBundle:Genre')->getGenreBySlugAndMedia($genreSlug, $mediaTypeSlug);
                    }catch(\Exception $ex){
                        throw new NotFoundHttpException ("There was a problem with that address");
                    }
                } 
               
            }else{
                $this->mediaSelection->setSelectedMediaGenres(null);
            }
            
            /*
             * if the keywords are set from a search then removed and another search performed
             * they should be removed from the MediaSelection object. 
             */
            if($keywords != null){// && $this->mediaSelection->getKeywords() == null){
                $this->mediaSelection->setKeywords($keywords);
            }
            
            if($computedKeywords != null){// && $computedKeywords != $this->mediaSelection->getComputedKeywords()){
                $this->mediaSelection->setComputedKeywords($computedKeywords);
            }
        }
        
        if(isset($params['page']))
            $this->mediaSelection->setPage($page);
            
        if($this->mediaSelection->getGenres() == null){
            $genres = $this->em->getRepository('SkNdMediaBundle:Genre')->getAllGenres();
            $this->mediaSelection->setGenres($genres);
        }
        
        //*****is necessary to merge the entities back into the entity manager after retrieving them
        if(!is_null($mediaType)){
            $mediaType = $this->em->merge($mediaType);
            $this->mediaSelection->setMediaTypes($mediaType);
        }
        if(!is_null($decade)){
            $decade = $this->em->merge($decade);
            $this->mediaSelection->setDecades($decade);
        }
        if(!is_null($genre)){
            $genre = $this->em->merge($genre);
            $this->mediaSelection->setSelectedMediaGenres($genre);
        }
        
        //final check to see if everything is still null
        if(is_null($mediaType) && is_null($decade) && is_null($genre))
            throw new RuntimeException('No media selection has been made');
        
        //save the data so this process is only done once
        $this->session->set('mediaSelection', $this->mediaSelection);
        
        return $this->mediaSelection;
   
        
    }
   
    /*
     * if no media, decade or genre is selected, defaults should be returned
     * so that the media selection form can be correctly set.
     */
    public function getMediaSelectionParams(){
        $mediaSelection = $this->session->get('mediaSelection');
        $params = array();
        if($mediaSelection != null){
            $params = array(
                    'media'     => $mediaSelection->getMediaTypes()->getSlug(),    
                    'decade'    => $mediaSelection->getDecades() != null ? $mediaSelection->getDecades()->getSlug() : Decade::$default,
                    'genre'     => $mediaSelection->getSelectedMediaGenres() != null ? $mediaSelection->getSelectedMediaGenres()->getSlug() : Genre::$default,
                    'keywords'  => $mediaSelection->getKeywords() != null ? $mediaSelection->getKeywords() : '-',
                    'page'      => $mediaSelection->getPage() != null ? $mediaSelection->getPage() : 1,
            );
        }else {
            $params = array(
                'media'     => MediaType::$default,
                'decade'    => Decade::$default,
                'genre'     => Genre::$default,
                'page'      => 1,
                'keywords'  => '-',
            );
        }
        
        //array_filter removes elements from an array based on the function defined as the second argument
        $params = Utilities::removeNullEntries($params);
        return $params;
        
    }
    
       
    /*
     * getDetails calls the api of the current strategy
     * but first gets recommendations from the db about that api
     * @param params - contains the relevant parameters to call the api. for amazon this is things
     * like Operation, Id. For youtube it contains things like keywords, decade, media etc
     */
    public function getDetails(array $params){
        //try getting the media selection from the session
        //$this->mediaSelection = $this->session->get('mediaSelection');
        $this->mediaSelection = $this->getMediaSelection();
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
    public function getListings(){
        //$this->mediaSelection = $this->session->get('mediaSelection');
        $this->mediaSelection = $this->getMediaSelection();
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
        $cachedListing->setComputedKeywords($this->mediaSelection->getComputedKeywords());
        $cachedListing->setPage($this->mediaSelection->getPage() != 1 ? $this->mediaSelection->getPage() : null);
        $cachedListing->setXmlData($response->asXML());        
        
        $this->em->persist($cachedListing);
        $this->flush();
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
                $this->flush();
            }
        }
        
        return $this->mediaResource;
    }
    
    //returns the current media resource or null if it doesn't exist
    public function getCurrentMediaResource(){
        return $this->mediaResource;
    }
    
    //once retrieved, if applicable, cache the resource
    public function cacheMediaResource(\SimpleXMLElement $xmlData, $itemId){
        if($this->mediaResource == null){
            //create a mediaresource
            $this->mediaResource = new MediaResource();
            $this->mediaResource->setId($itemId);
            $this->mediaResource->setAPI($this->em->getRepository('SkNdMediaBundle:API')->getAPIByName($this->apiStrategy->API_NAME));
            $this->mediaResource->setMediaType($this->mediaSelection->getMediaTypes());
            $this->mediaResource->setDecade($this->mediaSelection->getDecades());
            $this->mediaResource->setGenre($this->mediaSelection->getSelectedMediaGenres());
        } else {
            /**
             * if the media resource exists but was discovered using more specific parameters (i.e. mediatype, decade and genre)
             * set these parameters on the media resource. This means that items discovered using vague parameters become 
             * more precise over time
             **/ 
            if($this->mediaResource->getDecade() == null && $this->mediaSelection->getDecades() != null)
                $this->mediaResource->setDecade($this->mediaSelection->getDecades());
            
            if($this->mediaResource->getGenre() == null && $this->mediaSelection->getSelectedMediaGenres() != null)
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
        $this->flush();
        
    }
    
    //from a batch operation, take the xml data and resources and re-cache them
    public function cacheMediaResourceBatch(\SimpleXMLElement $xmlData, $mediaResources, $flush = true){
        $mr = $mediaResources->first();
        foreach($xmlData as $itemXml){
            //$cachedResource = $mr->getMediaResourceCache() != null ? $mr->getMediaResourceCache() : new MediaResourceCache();
            $cachedResource = new MediaResourceCache();
            $cachedResource->setId((string)$itemXml->ASIN);
            $cachedResource->setImageUrl($this->apiStrategy->getImageUrlFromXML($itemXml));
            $cachedResource->setTitle($this->apiStrategy->getItemTitleFromXML($itemXml));
            $cachedResource->setXmlData($itemXml->asXML());
            $cachedResource->setDateCreated(new \DateTime("now"));
            try{
                $mr->setMediaResourceCache($cachedResource);
                if($this->em->contains($mr))
                    $this->em->merge($mr);
                else
                    $this->em->persist($mr);
            } catch(\Exception $ex){
                throw $ex;
            }
            $mr = $mediaResources->next();
            
        }
        
        if($flush)
            $this->flush();
    }
    
    /**
     *
     * @param ArrayCollection $mediaResources 
     * @param int $page - optional page number to determine which results to process
     * @return null
     * @method processMediaResources checks through all media resources of 
     * a given memory wall, checks to see if valid cached data exists for them (newer than 24 hours)
     * deletes older cached records and loads uncached mediaresources from live api, then caches it 
     * For use by show memory wall and the timeline
     */
    public function processMediaResources($mediaResources, $page = 1){
        $updatesMade = false;
        //loop through each api, get the relevant media resources
        foreach($this->apis as $api){
            $this->setAPIStrategy($api->getName());
            
            $resources = $mediaResources->filter(function($mr) use ($api){
                //return mediaresources whos cache either doesn't exist or is older than 24 hours
                return $mr->getAPI()->getName() == $api->getName() && ($mr->getMediaResourceCache() == null || $mr->getMediaResourceCache()->getDateCreated()->format("Y-m-d H:i:s") < Utilities::getValidCreationTime());
            });
            if($resources->count() > 0){
                $ids = array();
                foreach($resources as $mediaResource){
                    array_push($ids, $mediaResource->getId());
                }
                //do batch process of ids then store in cache
                $this->response = $this->apiStrategy->doBatchProcess($ids);
                
                //cache the data using the collection of uncached resources, but don't flush yet
                $this->cacheMediaResourceBatch($this->response, $resources, false);
                
                //flush will remove all the older cached records from db and insert the new ones
                $this->flush();       
                $updatesMade = true;
            }
        }
        
        return $updatesMade;
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
    
    protected function flush(){
        $this->em->flush();
    }
    
    
    
}
?>
