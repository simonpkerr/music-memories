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
use SkNd\MediaBundle\MediaAPI\MediaAPI;
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
    private $title;
    
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
            !isset($params['itemId'])||
            !isset($params['title']))
            throw new \RuntimeException('required params not supplied for ' . get_class($this));
        
        $this->em = $params['em'];
        $this->mediaSelection = $params['mediaSelection'];
        $this->apiStrategy = $params['apiStrategy'];
        $this->itemId = $params['itemId'];
        $this->title = $params['title'];
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
        }
        
        $this->mediaResource = $this->categoriseMediaResource($this->mediaResource);
        
        return $this->mediaResource;
    }
    
    private function categoriseMediaResource(MediaResource $mr){
        //if decade null, try to refine based on title
        if(is_null($mr->getDecade())){
            $decade = Utilities::getDecadeSlugFromUrl($this->title);
            $decade = $this->em->getRepository('SkNdMediaBundle:Decade')->getDecadeBySlug($decade);
            if(!is_null($decade)){
                $mr->setDecade($decade);
            }
        }
        
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
            if($mr->getMediaType()->getSlug() == 'film-and-tv' && $this->mediaSelection->getMediaType()->getSlug() != 'film-and-tv')
                $mr->setMediaType($this->mediaSelection->getMediaType());

            if($mr->getDecade() == null && $this->mediaSelection->getDecade() != null)
                $mr->setDecade($this->mediaSelection->getDecade());

            if($mr->getGenre() == null && $this->mediaSelection->getSelectedMediaGenre() != null)
                $mr->setGenre($this->mediaSelection->getSelectedMediaGenre());
        }
        
        
        
        return $mr;
    }
    
    private function createNewMediaResource($itemId){
        $mediaResource = new MediaResource();
        $mediaResource->setId($itemId);
        $mediaResource->setAPI($this->apiStrategy->getAPIEntity()); 
        $mediaResource->setMediaType($this->mediaSelection->getMediaType());
        //decade classification is now done in the categoriseMediaResource method
        //$mediaResource->setDecade($this->mediaSelection->getDecade());
        //$mediaResource->setGenre($this->mediaSelection->getSelectedMediaGenre());
        
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
            $cachedResource->setXmlRef($this->createXmlRef($this->apiResponse, $this->apiStrategy->getName()));
            $cachedResource->setDateCreated(new \DateTime("now"));
            if(is_null($this->mediaResource->getDecade())){
                $decade = $this->apiStrategy->getDecadeFromXML($this->apiResponse);
                $decade = $this->em->getRepository('SkNdMediaBundle:Decade')->getDecadeBySlug($decade);
                if(!is_null($decade)){
                    $this->mediaResource->setDecade($decade);
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
    
    public function convertMedia(){
        $date = $this->apiStrategy->getValidCreationTime();
        $mrCollection = $this->em->createQuery('select mr from SkNd\MediaBundle\Entity\MediaResource mr where mr.api = :api AND mr.mediaResourceCache IS NOT NULL')
                ->setParameter('api', $this->apiStrategy->getAPIEntity())
                ->setMaxResults(1000)
                ->getResult();
       
        foreach ($mrCollection as $mr){
            $cache = $mr->getMediaResourceCache();
            if($cache->getDateCreated()->format("Y-m-d H:i:s") > $date){
                if(!is_null($cache->getRawXmlData())){
                    $cache->setXmlRef($this->createXmlRef($cache->getRawXmlData()));
                    $cache->setXmlData(null);
                }
            } else {
                $mr->setMediaResourceCache(null);
            }
            $this->em->persist($mr);
            
        }
        $this->em->flush();
    }
    
    public function createXmlRef(SimpleXMLElement $xmlData, $apiKey){
        //create the xml file and create a reference to it
        $apiRef = substr($apiKey,0,1);
        $timeStamp = new \DateTime("now");
        $timeStamp = $timeStamp->format("Y-m-d_H-i-s");
        $xmlRef = uniqid('d' . $apiRef . '-' . $timeStamp);
        $xmlData->asXML(MediaAPI::CACHE_PATH . $xmlRef . '.xml');
        
        return $xmlRef;
    }
    
}

?>
