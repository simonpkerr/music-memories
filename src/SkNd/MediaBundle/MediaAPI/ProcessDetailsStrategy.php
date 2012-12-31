<?php

/**
 * @abstract ProcessDetailsStrategy handles looking up of single media resource, getting details
 * without recommendations, processing cache and storing cache.
 * @copyright Simon Kerr 2012
 * @author Simon Kerr
 */

namespace SkNd\MediaBundle\MediaAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use Doctrine\ORM\EntityManager;
use SkNd\MediaBundle\MediaAPI\IAPIStrategy;
use SkNd\MediaBundle\Entity\MediaResource;
use SkNd\MediaBundle\Entity\MediaResourceCache;
use \SimpleXMLElement;

class ProcessDetailsStrategy implements IProcessMediaStrategy, IMediaDetails{
    protected $apiStrategy;
    protected $mediaSelection;
    protected $mediaResource;
    protected $em;
    protected $itemId;
    private $apiResponse;
    
    /**
     *
     * @param $params includes EntityManager $em, 
     * IAPIStrategy $apiStrategy, MediaSelection $mediaSelection,
     * itemId
     */
    public function __construct(array $params){
        if(!isset($params['em'])||
            !isset($params['mediaSelection'])||
            !isset($params['apiStrategy'])||
            !isset($params['itemId']))
            throw new \RuntimeException('required params not supplied for '. $this);
        
        
        $this->em = $params['em'];
        $this->mediaSelection = $params['mediaSelection'];
        $this->apiStrategy = $params['apiStrategy'];
        $this->itemId = $params['itemId'];
        //$this->mediaResource = null;
    }
    
    public function getMediaSelection(){
        return $this->mediaSelection;
    }
    
    public function getAPIData(){
        return $this->apiStrategy;
    }
    
    public function getMedia(){
        if(is_null($this->mediaResource))
            throw new \RuntimeException("MediaResource is null");
            
        return $this->mediaResource;
        
    }
    
    public function processMedia(){
        $this->mediaResource = $this->getMediaResource();
        
        if($this->mediaResource->getMediaResourceCache() == null){
            //look up the details from the api if not cached
            $this->apiResponse = $this->apiStrategy->getDetails(array('ItemId' => $this->itemId));
            /*$this->cacheMedia(array(
                'response'  =>  $response, 
                'itemId'    =>  $this->itemId));*/
        }
        
        //return array(
        //    'response'  =>  $response);
            //'itemId'    =>  $this->itemId);

    }
    
    public function getMediaResource(){
        $this->mediaResource = $this->em->getRepository('SkNdMediaBundle:MediaResource')->getMediaResourceById($this->itemId);
        if($this->mediaResource == null)
            $this->mediaResource = $this->createNewMediaResource($this->itemId);
        else {
            //is it necessary to delete immediately the cache or simply merge and updated version?
            $this->mediaResource = $this->processCache($this->mediaResource);
            /**
             * if the media resource exists but was discovered using more specific parameters (i.e. mediatype, decade and genre)
             * set these parameters on the media resource. This means that items discovered using vague parameters become 
             * more precise over time
             **/ 
            if($this->mediaResource->getDecade() == null && $this->mediaSelection->getDecade() != null)
                $this->mediaResource->setDecade($this->mediaSelection->getDecade());
            
            if($this->mediaResource->getGenre() == null && $this->mediaSelection->getSelectedMediaGenre() != null)
                $this->mediaResource->setGenre($this->mediaSelection->getSelectedMediaGenre());
        }
        
        return $this->mediaResource;
    }
    
    private function createNewMediaResource($itemId){
        $mediaResource = new MediaResource();
        $mediaResource->setId($itemId);
        $mediaResource->setAPI($this->apiStrategy->getAPIEntity()); //re-factor so that API entity exists in apiStrategy
        //$mediaResource->setAPI($this->mediaSelection->getAPI());
        $mediaResource->setMediaType($this->mediaSelection->getMediaType());
        $mediaResource->setDecade($this->mediaSelection->getDecade());
        $mediaResource->setGenre($this->mediaSelection->getSelectedMediaGenre());
        
        return $mediaResource;
    }
    
    private function processCache(MediaResource $mediaResource){
        if($mediaResource != null && $mediaResource->getMediaResourceCache() != null){
            //if a cached resource exists and is older than the threshold for the current api, delete it
            $dateCreated = $mediaResource->getMediaResourceCache()->getDateCreated();
            if($dateCreated->format("Y-m-d H:i:s") < $this->apiStrategy->getValidCreationTime()){
                $mediaResource->deleteMediaResourceCache();
                $this->em->flush();
            }
        }
        return $mediaResource;
    }
    
    public function cacheMedia(){
        if($this->mediaResource->getMediaResourceCache() == null){
            $cachedResource = new MediaResourceCache();
            $cachedResource->setId($this->mediaResource->getId());
            $cachedResource->setImageUrl($this->apiStrategy->getImageUrlFromXML($this->apiResponse));
            $cachedResource->setTitle($this->apiStrategy->getItemTitleFromXML($this->apiResponse));
            $cachedResource->setXmlData($this->apiResponse->asXML());
            $cachedResource->setDateCreated(new \DateTime("now"));
            $this->mediaResource->setMediaResourceCache($cachedResource);
        }

        $this->mediaResource->incrementViewCount();
        $this->persistMerge($this->mediaResource);
        $this->em->flush();
    }
        
    public function persistMerge($obj){
        if($this->em->contains($obj))
            $this->em->merge($obj);
        else
            $this->em->persist($obj);
    }
    
    
}

?>
