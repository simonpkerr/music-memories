<?php

/**
 * @abstract ProcessBatchStrategy
 * @copyright Simon Kerr 2012
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\MediaBundle\MediaAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use Doctrine\ORM\EntityManager;
use SkNd\MediaBundle\MediaAPI\IAPIStrategy;
use SkNd\MediaBundle\MediaAPI\MediaDetails;

class ProcessBatchStrategy implements IProcessMediaStrategy {
    protected $apis;
    protected $em;
    protected $mediaResources;
    private $apiResponses;
    //need mediaResource?
    
    /**
     * @param EntityManager $em, 
     * @param array $apis, 
     */
    public function __construct(array $params){
        $this->em = $params['em'];
        //$this->mediaSelection = $params['mediaSelection'];
        $this->apis = $params['apis'];
        $this->mediaResources = isset($params['mediaResources']) ? $params['mediaResources'] : null;
    }
    
    public function getAPIData(){
        return $this->apis;
    }
    
    public function getMedia(){
        //for show memory wall, nothing is required to be returned
        
        /*if(is_null($this->mediaResource))
            throw new \RuntimeException("MediaResource is null");
            
        return $this->mediaResource;*/
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
            //$this->setAPIStrategy($api->getName());
            
            $resources = array_filter($this->mediaResources, function($mr) use ($api){
                return $mr->getAPI()->getName() == $api->getName() && ($mr->getMediaResourceCache() == null || $mr->getMediaResourceCache()->getDateCreated()->format("Y-m-d H:i:s") < $api->getValidCreationTime());
            });
                        
            if(count($resources) > 0){
                $ids = array_keys($resources);
                
                //do batch process of ids 
                array_push($this->apiResponses, 
                        array(
                            'api'            => $api, 
                            'xmlData'        => $api->getBatch($ids),
                            'mediaResources' => $resources,
                        ));
                
                //cache the data using the collection of uncached resources, but don't flush yet
                //$this->cacheMediaResourceBatch($this->response, $resources, false);
                
                $updatesMade = true;
            }
        }
        //only flush when finished going through all records.
        //flush will update all the older cached records from db 
        //$this->em->flush();
        
        //return $updatesMade;
    }
    
    //from a batch operation, take the xml data and resources and re-cache them
    public function cacheMedia(){ 
    //public function cacheMediaResourceBatch(SimpleXMLElement $xmlData, array $mediaResources, $immediateFlush = true){
        /* all elements in the arrays are either existing mediaresources 
         * or the details mediaresource without cache
         */
        
        foreach($this->apiReponses as $apiResponse){
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
                        $this->persistMergeMediaResource($mr);
                    } catch(\Exception $ex){
                        throw $ex;
                    }
                } 
                //IS THIS NEEDED?
                ////else{
                    //otherwise create a new media resource and cache it
                    //$this->mediaResource = null;
                    
                    //HOW TO CONNECT CACHE MEDIA RESOURCE WHICH EXISTS IN PROCESSDETAILSSTRATEGY
                   // $this->cacheMediaResource($itemXml, $id, false);                
                //}
            }
        }
        
        
        
        $this->em->flush();
    
    }
    
    public function persistMergeMediaResource(MediaResource $mediaResource){
        if($this->em->contains($mediaResource))
            $this->em->merge($mediaResource);
        else
            $this->em->persist($mediaResource);
    }

    
}


?>
