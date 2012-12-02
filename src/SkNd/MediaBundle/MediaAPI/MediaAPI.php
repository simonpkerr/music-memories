<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaAPI controls access to the various apis and their operations,
 * checking for cached versions of details or listings
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\MediaAPI;

use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Session;
use Doctrine\ORM\EntityManager;
use SkNd\MediaBundle\MediaAPI\IAPIStrategy;
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\Entity\API;
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
use \SimpleXMLElement;

class MediaAPI {
    const MEDIA_RESOURCE_RECOMMENDATION = 1;
    const MEMORY_WALL_RECOMMENDATION = 2;
    
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
     * gets the current run mode, passes the doctrine object 
     * and an array of api objects
     */
    public function __construct($debug_mode, EntityManager $em, Session $session, array $apis){
        $this->setAPIs($apis);
        $this->debugMode = $debug_mode;
        $this->em = $em;
        $this->session = $session;
        //if(is_null($this->apiStrategy))
        //    $this->setAPIStrategy('amazonapi');
        
        $this->mediaSelection = $this->getMediaSelection();
        
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
    
    public function setAPIStrategy($apiKey){
        if(!array_key_exists($apiKey, $this->apis))
            throw new RuntimeException("api key not found");
            
        $this->apiStrategy = $this->apis[$apiKey]; 
    }
    
    public function getAPIStrategy($apiKey){
        if(!array_key_exists($apiKey, $this->apis))
            throw new RuntimeException("api key not found");
            
        return $this->apis[$apiKey]; 
         
    }
    
    public function getCurrentAPI(){
        return $this->apiStrategy;
    }
    
    
    
    public function setAPIs(array $apis){
        $this->apis = array_merge($apis);
    }
    
    //returns the current media resource or null if it doesn't exist
    public function getCurrentMediaResource(){
        return $this->mediaResource;
    }
    
    public function setMediaSelection(MediaSelection $mediaSelection){
        $this->mediaSelection = $mediaSelection;
        $this->session->set('mediaSelection', $this->mediaSelection);
    }
    
    /*
     * check session, return mediaSelection if not null
     * else create new media selection and return
     * $params - contains optional media, decade, genre, keywords, page
     * if params exist, they should override the session values if different
     */
    public function getMediaSelection(array $params = null){
        $api = null;
        $mediaTypeSlug = null;
        $decadeSlug = null;
        $genreSlug = null;
        $keywords = null;
        $computedKeywords = null;
        $page = 1;
        
        //try getting the media selection from the session
        if($this->mediaSelection == null)
            $this->mediaSelection = $this->session->has('mediaSelection') ? $this->session->get('mediaSelection') : new MediaSelection();
        
        $api = $this->mediaSelection->getAPI();
        $mediaType = $this->mediaSelection->getMediaType();
        $decade = $this->mediaSelection->getDecade();
        $genre = $this->mediaSelection->getSelectedMediaGenre();
        
        if($params != null){
            $apiSlug = isset($params['api']) ? $params['api'] : API::$default;
            $mediaTypeSlug = isset($params['media']) ? $params['media'] : MediaType::$default;
            $decadeSlug = isset($params['decade']) ? $params['decade'] : Decade::$default;
            $genreSlug = isset($params['genre']) ? $params['genre'] : Genre::$default;
            $keywords = isset($params['keywords']) ? $params['keywords'] != '-' ? $params['keywords'] : null : null;
            $computedKeywords = isset($params['computedKeywords']) ? $params['computedKeywords'] : null;
            $page = isset($params['page']) ? $params['page'] : $page;
            
            //only update the mediaSelection if different
            if($api == null || $apiSlug != $api->getName()){
                $api = $this->em->getRepository('SkNdMediaBundle:API')->getAPIByName($apiSlug);
                if($api == null)
                    throw new \RuntimeException("There was a problem with that api value");

                $this->setAPIStrategy($apiSlug);
            } 

            if($mediaType == null || $mediaTypeSlug != $mediaType->getSlug()){
                $mediaType = $this->em->getRepository('SkNdMediaBundle:MediaType')->getMediaTypeBySlug($mediaTypeSlug);
                if($mediaType == null)
                    throw new NotFoundHttpException("There was a problem with that address");
            }
       
            //if decade is not default decade and is not the same as existing decade
            if($decadeSlug != Decade::$default){
                if($decade == null || $decadeSlug != $decade->getSlug()){
                    $decade = $this->em->getRepository('SkNdMediaBundle:Decade')->getDecadeBySlug($decadeSlug);
                    if($decade == null)
                        throw new NotFoundHttpException ("There was a problem with that address");
                } 
            }else{
                $decade = null;
            }

            if($genreSlug != Genre::$default){
                //if the genre is different or the media type is different, reset the genre
                if($genre == null || $genreSlug != $genre->getSlug() || $mediaTypeSlug != $mediaType->getSlug()){
                    try{
                        $genre = $this->em->getRepository('SkNdMediaBundle:Genre')->getGenreBySlugAndMedia($genreSlug, $mediaTypeSlug);
                    }catch(\Exception $ex){
                        throw new NotFoundHttpException ("There was a problem with that address");
                    }
                } 
            }else{
                $genre = null;
            }
            
            /*
             * if the keywords are set from a search then removed and another search performed
             * they should be removed from the MediaSelection object. 
             */
            if($this->mediaSelection->getKeywords() != $keywords){
                $this->mediaSelection->setKeywords($keywords);
            }
            
            if($this->mediaSelection->getComputedKeywords() != $computedKeywords){
                $this->mediaSelection->setComputedKeywords($computedKeywords);
            }
        } else {
            //if no params were sent through, ensure that defaults are added for mediatype and api
            if($api == null){
                $api = $this->em->getRepository('SkNdMediaBundle:API')->getAPIByName(API::$default);
                $this->setAPIStrategy(API::$default);
            }
            if($mediaType == null){
                $mediaType = $this->em->getRepository('SkNdMediaBundle:MediaType')->getMediaTypeBySlug(MediaType::$default);
            }
            
        }
        
        if(isset($params['page']))
            $this->mediaSelection->setPage($page);
            
        if($this->mediaSelection->getGenres() == null){
            $genres = $this->em->getRepository('SkNdMediaBundle:Genre')->getAllGenres();
            $this->mediaSelection->setGenres($genres);
        }
        
        //*****is necessary to merge the entities back into the entity manager after retrieving them
        if(!is_null($api)){
            $api = $this->em->merge($api);
            $this->mediaSelection->setAPI($api);
        }
        
        if(!is_null($mediaType)){
            $mediaType = $this->em->merge($mediaType);
            $this->mediaSelection->setMediaType($mediaType);
        }
        
        if(!is_null($decade))
            $decade = $this->em->merge($decade);
        
        $this->mediaSelection->setDecade($decade);
                    
        if(!is_null($genre))
            $genre = $this->em->merge($genre);
        
        $this->mediaSelection->setSelectedMediaGenre($genre);
        
        //final check to see if everything is still null
        if(is_null($api) && is_null($mediaType))
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
        $params = array();
        if($this->mediaSelection != null){
            $params = array(
                    'media'     => $this->mediaSelection->getMediaType()->getSlug(),    
                    'decade'    => $this->mediaSelection->getDecade() != null ? $this->mediaSelection->getDecade()->getSlug() : Decade::$default,
                    'genre'     => $this->mediaSelection->getSelectedMediaGenre() != null ? $this->mediaSelection->getSelectedMediaGenre()->getSlug() : Genre::$default,
                    'keywords'  => $this->mediaSelection->getKeywords() != null ? $this->mediaSelection->getKeywords() : '-',
                    'page'      => $this->mediaSelection->getPage() != null ? $this->mediaSelection->getPage() : 1,
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
    
       
    /**
     * getDetails calls the api of the current strategy
     * but first gets recommendations from the db about that api
     * @param params - contains the relevant parameters to call the api. for amazon this is things
     * like ItemId. For youtube it contains things like keywords, decade, media etc
     * @param $recType - for details called from media controller, recommendations are required
     * however from the memory wall controller, when adding a resource to a memory wall, the item does 
     * not require recommendations to be looked up
     * 
     **/
    public function getMedia(IProcessStrategy $processStrategy){
        //$this->response = null;
        //$itemId = $params['ItemId'];
        //set the correct api from the controller
        //$apiReferrer = $params['apiReferrer'];
       
        //process batch strategy will not have a key?
        $this->setAPIStrategy($processStrategy->getAPIData());
        $processStrategy->setAPIStrategy($this->apiStrategy);
        $processStrategy->processMedia();
        $processStrategy->cacheMedia();
        
        return $processStrategy->getMedia();
        
        //if recommendations are required, decorate the mediadetails object
        //if($getRecommendations){
        //    $mediaDetails = new MediaDetailsRecommendationDecorator($mediaDetails);
       // }
        //$this->mediaResource = $mediaDetails->getDetails($itemId);
        
        
        /*//look up the mediaResource in the db and fetch associated cached object
        $this->mediaResource = $this->getMediaResource($itemId);
        //if no recommendations are required
        if(is_null($recType)){
            //look up the details from the api if not cached
            if($this->mediaResource == null || $this->mediaResource->getMediaResourceCache() == null){
                $this->response = $this->apiStrategy->getDetails($params, $this->mediaSelection);
                //cache the data
                $this->cacheMediaResource($this->response, $itemId);
            } 
        }else{*/

            //get the media resource recommendations based on the media selection, returns 2 arrays for generic and exact matches
            //$recommendations = $this->getRecommendations($recType, $itemId);
            
            //get all media resources into one array for processing
            //$allMediaResources = array_merge(array($itemId => $this->mediaResource), $recommendations['genericMatches'], $recommendations['exactMatches']);

            //process all the resources, which filters mr's based on uncached ones, then does a batch job
        //    $this->processMediaResources($allMediaResources);
            
       //     $this->mediaResource->setRelatedMediaResources($recommendations);
        //}
        
        
        
    }
    
    
    /**
     * getListings calls the api of the current strategy
     * but first checks to see if the query is in the listings cache table along with results
     * @param recType - recommendation type for listings, can be either memory walls returned for amazon listings
     * or null for youtube listings.
     * @return array(response, recommendations)
     * 
     **/
    public function getListings($recType = null){
        $this->response = null;
           
        $this->response = $this->getCachedListings();
        $this->cachedDataExist = $this->response != null ? true : false;
        
        //look up the query from the db and return cached listings if available
        if(!$this->cachedDataExist){
            $this->response = $this->apiStrategy->getListings($this->mediaSelection);
            
            //once results are retrieved insert into cache
            $this->cacheListings($this->response);
        } 
        
        $recommendations = $this->getRecommendations($recType);
        return array(
            'response'          => $this->response,
            'recommendations'   => $recommendations,
        );
        
                
    }
    
    //only results returned from the live api are cached
    public function cacheListings(SimpleXMLElement $response){
        $cachedListing = new \SkNd\MediaBundle\Entity\MediaResourceListingsCache();
        $cachedListing->setAPI($this->em->getRepository('SkNdMediaBundle:API')->getAPIByName($this->apiStrategy->getName()));
        $cachedListing->setMediaType($this->mediaSelection->getMediaType());
        $cachedListing->setDecade($this->mediaSelection->getDecade());
        $cachedListing->setGenre($this->mediaSelection->getSelectedMediaGenre());
        $cachedListing->setKeywords($this->mediaSelection->getKeywords());
        $cachedListing->setComputedKeywords($this->mediaSelection->getComputedKeywords());
        $cachedListing->setPage($this->mediaSelection->getPage() != 1 ? $this->mediaSelection->getPage() : null);
        $cachedListing->setXmlData($response->asXML());        
        
        $this->em->persist($cachedListing);
        $this->flush();
    }
    
    public function getCachedListings(){
        //look up the MediaResourceListingsCache with the params and the current apistrategy name   
        $xmlResponse = $this->em->getRepository('SkNdMediaBundle:MediaResourceListingsCache')->getCachedListings($this->mediaSelection, $this->apiStrategy);
        
        if($xmlResponse != null){
            return @simplexml_load_string($xmlResponse);
        }
        else{
            return null;
        }
    }
    
    /**
     * gets recommendations for either the listings or details pages
     * @param MediaSelection $mediaSelection
     * @param $itemId is used so that the selected item is not picked as a recommendation
     * @return $recommendatations array
     */
//    public function getRecommendations($recType = null, $id = null) {
//        $recommendationSet = null;
//        
//        if($recType == self::MEMORY_WALL_RECOMMENDATION){
//            if($this->mediaSelection->getDecade() != null){
//                $recommendationSet = $this->em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallsByDecade($this->mediaSelection->getDecade());
//                if(count($recommendationSet) > 0)
//                    return $recommendationSet;
//            }
//        }
//        
//        /*if($recType == self::MEDIA_RESOURCE_RECOMMENDATION){
//            
//            $recommendationSet = $this->em->getRepository('SkNdMediaBundle:MediaResource')->getMediaResourceRecommendations($this->mediaSelection, $id);
//            return $recommendationSet;
//        }*/
//        
//        return null;
//        
//    }
   
    //get an individual media resource based on item id and retrieve or delete associated cached resource
    /*public function getMediaResource($itemId){
        $mediaResource = $this->em->getRepository('SkNdMediaBundle:MediaResource')->getMediaResourceById($itemId);
        if($mediaResource == null)
            $mediaResource = $this->createNewMediaResource($itemId);
        else {
            $mediaResource = $this->processMediaResourceCache($mediaResource);
          
            if($mediaResource->getDecade() == null && $this->mediaSelection->getDecade() != null)
                $mediaResource->setDecade($this->mediaSelection->getDecade());
            
            if($mediaResource->getGenre() == null && $this->mediaSelection->getSelectedMediaGenre() != null)
                $mediaResource->setGenre($this->mediaSelection->getSelectedMediaGenre());
        }
        
        return $mediaResource;
    }*/

    /*private function processMediaResourceCache($mediaResource){
        if($mediaResource != null && $mediaResource->getMediaResourceCache() != null){
            //if a cached resource exists and is older than the threshold for the current api, delete it
            $dateCreated = $mediaResource->getMediaResourceCache()->getDateCreated();
            if($dateCreated->format("Y-m-d H:i:s") < $this->apiStrategy->getValidCreationTime()){
                $mediaResource->deleteMediaResourceCache();
                $this->flush();
            }
        }
        return $mediaResource;
    }*/
    
    /*private function createNewMediaResource($itemId){
        $mediaResource = new MediaResource();
        $mediaResource->setId($itemId);
        $mediaResource->setAPI($this->mediaSelection->getAPI());
        $mediaResource->setMediaType($this->mediaSelection->getMediaType());
        $mediaResource->setDecade($this->mediaSelection->getDecade());
        $mediaResource->setGenre($this->mediaSelection->getSelectedMediaGenre());
        
        return $mediaResource;
    }*/
    
    //once retrieved, if applicable, cache the resource
    /*public function cacheMediaResource(SimpleXMLElement $xmlData, $itemId, $immediateFlush = true){
        if($this->mediaResource == null)
            $this->mediaResource = $this->createNewMediaResource($itemId);
        
        //if cached listings not exists create a new MediaResourceCache object and update the mediaResource    
        if($this->mediaResource->getMediaResourceCache() == null){
            $cachedResource = new MediaResourceCache();
            $cachedResource->setId($this->mediaResource->getId());
            $cachedResource->setImageUrl($this->apiStrategy->getImageUrlFromXML($xmlData));
            $cachedResource->setTitle($this->apiStrategy->getItemTitleFromXML($xmlData));
            $cachedResource->setXmlData($xmlData->asXML());
            $cachedResource->setDateCreated(new \DateTime("now"));
            $this->mediaResource->setMediaResourceCache($cachedResource);
        }

        $this->mediaResource->incrementViewCount();
        
        $this->persistMergeMediaResource($this->mediaResource);
        
        if($immediateFlush)
            $this->flush();
        
    }*/
    
    /*private function persistMergeMediaResource($mediaResource){
        if($this->em->contains($mediaResource))
            $this->em->merge($mediaResource);
        else
            $this->em->persist($mediaResource);
    }*/
    
    //from a batch operation, take the xml data and resources and re-cache them
//    public function cacheMediaResourceBatch(SimpleXMLElement $xmlData, array $mediaResources, $immediateFlush = true){
//        foreach($xmlData as $itemXml){
//            $id = $this->apiStrategy->getIdFromXML($itemXml);
//            //if media resource exists, re-cache the data
//            if(isset($mediaResources[$id])){
//                $mr = $mediaResources[$id];
//                $cachedResource = new MediaResourceCache();
//                $cachedResource->setId($id);
//                $cachedResource->setImageUrl($this->apiStrategy->getImageUrlFromXML($itemXml));
//                $cachedResource->setTitle($this->apiStrategy->getItemTitleFromXML($itemXml));
//                $cachedResource->setXmlData($this->apiStrategy->getXML($itemXml));
//                $cachedResource->setDateCreated(new \DateTime("now"));
//                try{
//                    $mr->setMediaResourceCache($cachedResource);
//                    $this->persistMergeMediaResource($mr);
//                } catch(\Exception $ex){
//                    throw $ex;
//                }
//            } else{
//                //otherwise create a new media resource and cache it
//                $this->mediaResource = null;
//                $this->cacheMediaResource($itemXml, $id, false);                
//            }
//        }
//        
//        if($immediateFlush)
//            $this->flush();
//    }
    /**
    
     *
     * @param array $mediaResources 
     * @param int $page - optional page number to determine which results to process
     * @return null
     * @method processMediaResources checks through all media resources of 
     * a given memory wall, checks to see if valid cached data exists for them (newer than 24 hours)
     * deletes older cached records and loads uncached mediaresources from live api, then caches it 
     * For use by show memory wall and the timeline
     */
//    public function processMediaResources(array $mediaResources, $page = 1){
//        $updatesMade = false;
//        //loop through each api, get the relevant media resources
//        foreach($this->apis as $api){
//            $this->setAPIStrategy($api->getName());
//            
//            $resources = array_filter($mediaResources, function($mr) use ($api){
//                return $mr->getAPI()->getName() == $api->getName() && ($mr->getMediaResourceCache() == null || $mr->getMediaResourceCache()->getDateCreated()->format("Y-m-d H:i:s") < $api->getValidCreationTime());
//            });
//                        
//            if(count($resources) > 0){
//                $ids = array_keys($resources);
//                
//                //do batch process of ids then store in cache
//                $this->response = $this->apiStrategy->getBatch($ids);
//                
//                //cache the data using the collection of uncached resources, but don't flush yet
//                $this->cacheMediaResourceBatch($this->response, $resources, false);
//                
//                $updatesMade = true;
//            }
//        }
//        //only flush when finished going through all records.
//        //flush will update all the older cached records from db 
//        $this->flush();
//        
//        return $updatesMade;
//    }
    
    
    /*protected function flush(){
        $this->em->flush();
    }*/
    
    
    
}
?>
