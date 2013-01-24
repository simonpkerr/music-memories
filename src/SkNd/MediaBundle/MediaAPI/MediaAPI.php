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
use Symfony\Component\HttpFoundation\Session\Session;
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
        
        $this->debugMode = $debug_mode;
        $this->em = $em;
        $this->session = $session;
        $this->setAPIs($apis);        
        $this->mediaSelection = $this->setMediaSelection();
        
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
    
    //set the apis and attach the entity to each api
    public function setAPIs(array $apis){
        $this->apis = array_merge($apis);
        foreach($this->apis as $api){
            $api->setAPIEntity($this->em->getRepository('SkNdMediaBundle:API')->getAPIByName($api->getName()));
        }
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
    
    //returns the current media resource or null if it doesn't exist
    public function getCurrentMediaResource(){
        return $this->mediaResource;
    }
    
    
    /*
     * check session, return mediaSelection if not null
     * else create new media selection and return
     * $params - contains optional mediaSelection object - if this is set,
     * simply set the mediaselection as the passed object
     * otherwise
     * @param array(api (e.g.amazonapi), media (e.g.film), decade (e.g.1980), genre (e.g. comedy), keywords, computedKeywords, page)
     * if params exist, they should override the session values if different
     */    
    public function setMediaSelection(array $params = null){
        $api = null;
        $mediaTypeSlug = null;
        $decadeSlug = null;
        $genreSlug = null;
        $keywords = null;
        $computedKeywords = null;
        $page = 1;
        
        if(isset($params['mediaSelection']) && $params['mediaSelection'] instanceof MediaSelection){
            $this->mediaSelection = $params['mediaSelection'];
            $this->session->set('mediaSelection', $this->mediaSelection);
        }else{
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
                        throw new NotFoundHttpException("There was a problem with that media type");
                }

                //if decade is not default decade and is not the same as existing decade
                if($decadeSlug != Decade::$default){
                    if($decade == null || $decadeSlug != $decade->getSlug()){
                        $decade = $this->em->getRepository('SkNdMediaBundle:Decade')->getDecadeBySlug($decadeSlug);
                        if($decade == null)
                            throw new NotFoundHttpException ("There was a problem with that decade");
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
                            throw new NotFoundHttpException ("There was a problem with that genre");
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
        
    }
    
    public function getMediaSelection(){
        if(!is_null($this->mediaSelection))
            return $this->mediaSelection;
        
        if($this->session->has('mediaSelection')){
            $this->mediaSelection = $this->session->get('mediaSelection');
        }else{
            $this->mediaSelection = $this->setMediaSelection();
            $this->session->set('mediaSelection', $this->mediaSelection);
        }
        
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
                'api'       => !is_null($this->mediaSelection->getAPI()) ? $this->mediaSelection->getAPI()->getName() : API::$default,    
                'media'     => $this->mediaSelection->getMediaType()->getSlug(),    
                'decade'    => !is_null($this->mediaSelection->getDecade()) ? $this->mediaSelection->getDecade()->getSlug() : Decade::$default,
                'genre'     => !is_null($this->mediaSelection->getSelectedMediaGenre()) ? $this->mediaSelection->getSelectedMediaGenre()->getSlug() : Genre::$default,
                'keywords'  => !is_null($this->mediaSelection->getKeywords()) ? $this->mediaSelection->getKeywords() : null,
                'page'      => !is_null($this->mediaSelection->getPage()) ? $this->mediaSelection->getPage() : 1,
            );
        }else {
            $params = array(
                'api'       => API::$default,    
                'media'     => MediaType::$default,
                'decade'    => Decade::$default,
                'genre'     => Genre::$default,
                'keywords'  => null,
                'page'      => 1,
            );
        }
        
        //array_filter removes elements from an array based on the function defined as the second argument
        $params = Utilities::removeNullEntries($params);
        return $params;
        
    }
    
       
    /**
     * getMedia calls the api of the current strategy
     * but first gets recommendations from the db about that api
     * @param params - contains the relevant parameters to call the api. for amazon this is things
     * like ItemId. For youtube it contains things like keywords, decade, media etc
     * @param $recType - for details called from media controller, recommendations are required
     * however from the memory wall controller, when adding a resource to a memory wall, the item does 
     * not require recommendations to be looked up
     * 
     **/
    public function getMedia(IProcessMediaStrategy $processStrategy){
        
        $processStrategy->processMedia();
        $processStrategy->cacheMedia();
        
        return $processStrategy->getMedia();     
        
    } 
}
?>
