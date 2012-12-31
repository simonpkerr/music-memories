<?php

/**
 * @abstract ProcessBatchStrategy
 * @uses MemoryWallController show action, MediaController details action
 * @copyright Simon Kerr 2012
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\MediaBundle\MediaAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\MediaResourceCache;
use Doctrine\ORM\EntityManager;
use SkNd\MediaBundle\MediaAPI\IAPIStrategy;
use SkNd\MediaBundle\MediaAPI\MediaDetails;

class ProcessBatchStrategy implements IProcessMediaStrategy, IMediaDetails {
    protected $apis;
    protected $em;
    protected $mediaResources;
    protected $apiResponses;
    
    /**
     * @param EntityManager $em, 
     * @param array $apis, 
     */
    public function __construct(array $params){
        $this->em = $params['em'];
        $this->apis = $params['apis'];
        if(isset($params['mediaResources']))
            $this->mediaResources = $params['mediaResources'];
        
        $this->apiResponses = array();
    }
    
    public function getMediaSelection(){
        return null;
    }
    
    public function getAPIData(){
        return null;
    }
    
    public function getMedia(){
        //for show memory wall, nothing is required to be returned
        
        if(is_null($this->mediaResources))
            throw new \RuntimeException("MediaResources are null");
            
        return $this->mediaResources;
    }
    
    /**
     * @param array $mediaResources 
     * @param int $page - optional page number to determine which results to process
     * @return null
     * @method processMediaResources checks through all media resources in the array
     * ,checks to see if valid cached data exists for them (newer than 24 hours)
     * deletes older cached records and loads uncached mediaresources from live api, then caches it 
     * @uses show memory wall; the timeline; recommendations
     */
    public function processMedia(){
        $updatesMade = false;
        //loop through each api, get the relevant media resources
        foreach($this->apis as $api){         
            $resources = array_filter($this->mediaResources, function($mr) use ($api){
                return $mr->getAPI()->getName() == $api->getName() && ($mr->getMediaResourceCache() == null || $mr->getMediaResourceCache()->getDateCreated()->format("Y-m-d H:i:s") < $api->getValidCreationTime());
            });
                        
            if(count($resources) > 0){
                $ids = array_keys($resources);
                
                //do batch process of ids 
                array_push($this->apiResponses, array(
                            'api'            => $api, 
                            'xmlData'        => $api->getBatch($ids),
                            'mediaResources' => $resources,
                        ));
                
                $updatesMade = true;
            }
        }

    }
    
    //from a batch operation, take the xml data and resources and re-cache them
    public function cacheMedia(){ 
    //public function cacheMediaResourceBatch(SimpleXMLElement $xmlData, array $mediaResources, $immediateFlush = true){
        /* all elements in the arrays are either existing mediaresources 
         * or the details mediaresource without cache
         */
        
        foreach($this->apiResponses as $apiResponse){
            $api = $apiResponse['api'];
            $mediaResources = $apiResponse['mediaResources'];
            
            foreach($apiResponse['xmlData'] as $itemXml){
                $id = $api->getIdFromXML($itemXml);
                //if media resource exists, re-cache the data
                if(isset($mediaResources[$id])){
                    $mr = $mediaResources[$id];
                    $cachedResource = new MediaResourceCache();
                    $cachedResource->setId($id);
                    $cachedResource->setImageUrl($api->getImageUrlFromXML($itemXml));
                    $cachedResource->setTitle($api->getItemTitleFromXML($itemXml));
                    $cachedResource->setXmlData($api->getXML($itemXml));
                    $cachedResource->setDateCreated(new \DateTime("now"));
                    try{
                        $mr->setMediaResourceCache($cachedResource);
                        $this->persistMerge($mr);
                    } catch(\Exception $ex){
                        throw $ex;
                    }
                } 
                
            }
        }
        
        
        
        $this->em->flush();
    
    }
    
    public function persistMerge($obj){
        if($this->em->contains($obj))
            $this->em->merge($obj);
        else
            $this->em->persist($obj);
    }
    
    //not needed
    public function getMediaResource(){
        return null;
    }

    
}


?>
