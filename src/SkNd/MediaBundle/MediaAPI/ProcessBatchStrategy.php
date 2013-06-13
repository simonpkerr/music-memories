<?php

/**
 * @abstract ProcessBatchStrategy
 * @uses MemoryWallController show action, MediaController details action
 * @copyright Simon Kerr 2012
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\MediaBundle\MediaAPI;
use SkNd\MediaBundle\Entity\MediaResourceCache;
use Doctrine\ORM\EntityManager;
use SkNd\MediaBundle\MediaAPI\MediaAPI;
use SkNd\MediaBundle\MediaAPI\Utilities;

class ProcessBatchStrategy implements IProcessMediaStrategy, IMediaDetails {
    protected $apis;
    protected $em;
    protected $mediaResources;
    protected $apiResponses;
    protected $utilities;
    
    /**
     * @param EntityManager $em, 
     * @param array $apis, 
     * @param [mediaResources] - passed from MemoryWallController for showWall,
     * calculated from processDetailsDecoratorStrategy processMedia method
     * 
     */
    public function __construct(array $params){
        if(!isset($params['em'])||
           !isset($params['apis']))
            throw new \RuntimeException('required params not supplied for '. get_class($this));
        
        $this->em = $params['em'];
        $this->apis = $params['apis'];
        if(isset($params['mediaResources']))
            $this->mediaResources = $params['mediaResources'];
        
        $this->apiResponses = array();
        $this->utilities = new Utilities();
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
    
    private function getAllMediaResources(){
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
        
        $mrs = $this->getAllMediaResources();
        
        //loop through each api, get the relevant media resources
        foreach($this->apis as $api){         
            $resources = array_filter($mrs, function($mr) use ($api){
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
                    //delete the old xml file
                    $cachedResource->deleteXmlRef();
                    $cachedResource->setXmlRef($this->createXmlRef($itemXml, $mr->getAPI()->getName()));
                    $cachedResource->setDateCreated(new \DateTime("now"));
                    if(is_null($mr->getDecade())){
                        $decade = $api->getDecadeFromXML($itemXml);
                        $decade = $this->em->getRepository('SkNdMediaBundle:Decade')->getDecadeBySlug($decade);
                        if(!is_null($decade)){
                            $mr->setDecade($decade);
                        }
                    }
                    try{
                        $mr->setMediaResourceCache($cachedResource);
                        $this->persistMergeFlush($mr, false);
                        /*
                         * if the original media resource was updated, insert it back
                         * into the mediaResources array
                         */
                        $this->mediaResources[$mr->getId()] = $mr;
                    } catch(\Exception $ex){
                        throw $ex;
                    }
                } 
                
            }
        }
       
        $this->persistMergeFlush();
    
    }
    
    public function persistMergeFlush($obj = null, $immediateFlush = true){
        $this->utilities->persistMergeFlush($this->em, $obj, $immediateFlush);
    }
    
    
    //not needed
    public function getMediaResource(){
        return null;
    }
    
    public function createXmlRef(\SimpleXMLElement $xmlData, $apiKey){
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
