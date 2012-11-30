<?php

/**
 * @abstract MediaDetailsRecommendationDecorator takes a MediaDetails object as a constructor param 
 * and decorates its functionality by looking up recommendations
 * @copyright Simon Kerr 2012
 * @author Simon Kerr
 */
namespace SkNd\MediaBundle\MediaAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use Doctrine\ORM\EntityManager;
use SkNd\MediaBundle\MediaAPI\IAPIStrategy;
use SkNd\MediaBundle\MediaAPI\MediaDetails;

class ProcessBatchStrategy implements IProcessMediaStrategy {
    protected $apiStrategyList;
    protected $mediaResource;
    protected $em;
    protected $itemId;
    
    /**
     * @param array $params includes MediaDetails $mediaDetails,
     * EntityManager $em, 
     * IAPIStrategy $apiStrategy, 
     * MediaSelection $mediaSelection,
     * itemId
     */
    public function __construct(array $params){
        $this->mediaDetails = $params['mediaDetails'];
        $this->em = $params['em'];
        $this->mediaSelection = $params['mediaSelection'];
        $this->apiStrategy = $params['apiStrategy'];
        $this->itemId = $params['itemId'];
    }
    
    public function processMedia(){
        $this->mediaResource = $this->getMediaResource($this->itemId);
        $recommendations = $this->getRecommendations($this->itemId);
        //get all media resources into one array for processing
        $allMediaResources = array_merge(array($this->itemId => $this->mediaResource), $recommendations['genericMatches'], $recommendations['exactMatches']);
        //process all the resources, which filters mr's based on uncached ones, then does a batch job
        parent::processMedia($allMediaResources);
        $this->mediaResource->setRelatedMediaResources($recommendations);
        
        return $this->mediaResource;
    }
    
    public function cacheMedia(array $params){
        parent::cacheMedia($params);
    }
    
    /**
     * details recommendations needs to look at the mediaselection and try to get media resources based on all params
     * then just on decade, and then based on the users age
     * @param $itemId is used so that the selected item is not picked as a recommendation
     * @return $recommendatations array
     */
    private function getRecommendations($itemId) {
        $recommendationSet = $this->em->getRepository('SkNdMediaBundle:MediaResource')->getMediaResourceRecommendations($this->mediaSelection, $itemId);
        return $recommendationSet;
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
    public function processMediaResources(array $mediaResources, $page = 1){
        $updatesMade = false;
        //loop through each api, get the relevant media resources
        foreach($this->apis as $api){
            $this->setAPIStrategy($api->getName());
            
            $resources = array_filter($mediaResources, function($mr) use ($api){
                return $mr->getAPI()->getName() == $api->getName() && ($mr->getMediaResourceCache() == null || $mr->getMediaResourceCache()->getDateCreated()->format("Y-m-d H:i:s") < $api->getValidCreationTime());
            });
                        
            if(count($resources) > 0){
                $ids = array_keys($resources);
                
                //do batch process of ids then store in cache
                $this->response = $this->apiStrategy->getBatch($ids);
                
                //cache the data using the collection of uncached resources, but don't flush yet
                $this->cacheMediaResourceBatch($this->response, $resources, false);
                
                $updatesMade = true;
            }
        }
        //only flush when finished going through all records.
        //flush will update all the older cached records from db 
        $this->em->flush();
        
        return $updatesMade;
    }
    
    public function getMediaResource($itemId){
        return $this->mediaDetails->getMediaResource($itemId);
    }
    
    public function persistMergeMediaResource($mediaResource){
        $this->mediaDetails->persistMergeMediaResource($mediaResource);
    }
    
    
}


?>
