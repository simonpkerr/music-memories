<?php

/**
 * @abstract MediaDetails handles looking up of media resources, getting details
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

class MediaDetails implements IMediaDetails{
    protected $apiStrategy;
    protected $mediaSelection;
    protected $mediaResource;
    protected $em;
    
    public function __construct(EntityManager $em, IAPIStrategy $apiStrategy, MediaSelection $mediaSelection){
        $this->em = $em;
        $this->mediaSelection = $mediaSelection;
        $this->apiStrategy = $apiStrategy;
    }
    
    public function getDetails($itemId){
        //look up the mediaResource in the db and fetch associated cached object
        $this->mediaResource = $this->getMediaResource($itemId);
        
        if($this->mediaResource->getMediaResourceCache() == null){
            //look up the details from the api if not cached
            $response = $this->apiStrategy->getDetails($itemId);
            $this->cacheMediaResource($response, $itemId);
        }
        
        return $this->mediaResource;
    }
    
    public function getMediaResource($itemId){
        $this->mediaResource = $this->em->getRepository('SkNdMediaBundle:MediaResource')->getMediaResourceById($itemId);
        if($this->mediaResource == null)
            $this->mediaResource = $this->createNewMediaResource($itemId);
        else {
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
        $mediaResource->setAPI($this->apiStrategy->getAPI());
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
                $this->flush();
            }
        }
        return $mediaResource;
    }
    
    public function cacheMediaResource(SimpleXMLElement $response, $itemId){
        // not needed as is in getDetails method
        if($this->mediaResource == null)
            $this->mediaResource = $this->createNewMediaResource($itemId);
        
        //if cached listings not exists create a new MediaResourceCache object and update the mediaResource    
        if($this->mediaResource->getMediaResourceCache() == null){
            $cachedResource = new MediaResourceCache();
            $cachedResource->setId($this->mediaResource->getId());
            $cachedResource->setImageUrl($this->apiStrategy->getImageUrlFromXML($response));
            $cachedResource->setTitle($this->apiStrategy->getItemTitleFromXML($response));
            $cachedResource->setXmlData($response->asXML());
            $cachedResource->setDateCreated(new \DateTime("now"));
            $this->mediaResource->setMediaResourceCache($cachedResource);
        }

        $this->mediaResource->incrementViewCount();
        
        $this->persistMergeMediaResource($this->mediaResource);
        
        $this->flush();
        
    }
        
    public function persistMergeMediaResource($mediaResource){
        if($this->em->contains($mediaResource))
            $this->em->merge($mediaResource);
        else
            $this->em->persist($mediaResource);
    }
    
    public function flush(){
        $this->em->flush();
    }
    
}

?>
