<?php

/**
 * @abstract ProcessDetailsStrategy handles looking up of single media resource, getting details
 * without recommendations, processing cache and storing cache.
 * @copyright Simon Kerr 2012
 * @author Simon Kerr
 * @uses MemoryWallController addMediaResource, MediaController getDetails (param of decorator strategy)
 * 
 */

namespace SkNd\MediaBundle\MediaAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use Doctrine\ORM\EntityManager;
use SkNd\MediaBundle\MediaAPI\IAPIStrategy;
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\Entity\MediaResource;
use SkNd\MediaBundle\Entity\MediaResourceCache;
use \SimpleXMLElement;

class ProcessDetailsStrategy implements IProcessMediaStrategy, IMediaDetails {
    protected $apiStrategy;
    protected $mediaSelection;
    protected $mediaResource;
    protected $em;
    protected $utilities;
    protected $itemId;
    private $apiResponse;
    private $referrer;
    
    /**
     *
     * @param $params includes EntityManager $em, 
     * IAPIStrategy $apiStrategy, MediaSelection $mediaSelection,
     * itemId, referrer (which url the request has come from)
     */
    public function __construct(array $params){
        if(!isset($params['em'])||
            !isset($params['mediaSelection'])||
            !isset($params['apiStrategy'])||
            !isset($params['itemId']))
            throw new \RuntimeException('required params not supplied for ' . get_class($this));
        
        $this->em = $params['em'];
        $this->mediaSelection = $params['mediaSelection'];
        $this->apiStrategy = $params['apiStrategy'];
        $this->itemId = $params['itemId'];
        $this->referrer = isset($params['referrer']) ? $params['referrer'] : null;
        
        $this->utilities = new Utilities();
        
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
        }
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
             * 
             * IMPORTANT - only update the media resource if the referrer was the search method, otherwise could cause
             * wrong categorisation of resources. (if a vague search was performed, an item added, a more specific search done,
             * then the item viewed again through a memory wall or direct referral, it could potentially be refined wrongly.
             **/ 
            if(!is_null($this->referrer) && strpos($this->referrer, 'search') !== false){
                if($this->mediaResource->getMediaType()->getSlug() == 'film-and-tv' && $this->mediaSelection->getMediaType()->getSlug() != 'film-and-tv')
                    $this->mediaResource->setMediaType($this->mediaSelection->getMediaType());

                if($this->mediaResource->getDecade() == null && $this->mediaSelection->getDecade() != null)
                    $this->mediaResource->setDecade($this->mediaSelection->getDecade());

                if($this->mediaResource->getGenre() == null && $this->mediaSelection->getSelectedMediaGenre() != null)
                    $this->mediaResource->setGenre($this->mediaSelection->getSelectedMediaGenre());
            }
        }
        
        return $this->mediaResource;
    }
    
    private function createNewMediaResource($itemId){
        $mediaResource = new MediaResource();
        $mediaResource->setId($itemId);
        $mediaResource->setAPI($this->apiStrategy->getAPIEntity()); 
        $mediaResource->setMediaType($this->mediaSelection->getMediaType());
        $mediaResource->setDecade($this->mediaSelection->getDecade());
        $mediaResource->setGenre($this->mediaSelection->getSelectedMediaGenre());
        
        return $mediaResource;
    }
    
    private function processCache(MediaResource $mediaResource){
        if($mediaResource->getMediaResourceCache() != null){
            //if a cached resource exists and is older than the threshold for the current api, delete it
            $dateCreated = $mediaResource->getMediaResourceCache()->getDateCreated();
            if($dateCreated->format("Y-m-d H:i:s") < $this->apiStrategy->getValidCreationTime()){
                $mediaResource->deleteMediaResourceCache();
                $this->persistMergeFlush();
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
            if(is_null($this->mediaResource->getDecade())){
                $decade = $this->apiStrategy->getDecadeFromXML($this->apiResponse);
                if(!is_null($decade)){
                    $this->mediaResource->setDecade($this->em->getRepository('SkNdMediaBundle:Decade')->getDecadeBySlug($decade));
                }
            }
            $this->mediaResource->setMediaResourceCache($cachedResource);
        }

        $this->mediaResource->incrementViewCount();
        $this->persistMergeFlush($this->mediaResource);
    }
        
    public function persistMergeFlush($obj = null, $immediateFlush = true){
        $this->utilities->persistMergeFlush($this->em, $obj, $immediateFlush);
    }
    
    
}

?>
