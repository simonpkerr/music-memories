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

class ProcessDetailsDecoratorStrategy extends ProcessBatchStrategy implements IProcessMediaStrategy, IMediaDetails {
    protected $processDetailsStrategy;
    //protected $apiStrategy;
    //protected $mediaSelection;
    protected $mediaResource;
    protected $em;
    //protected $itemId;
    
    /**
     * @param array $params includes MediaDetails $mediaDetails,
     * EntityManager $em, 
     * IAPIStrategy $apiStrategy, 
     * MediaSelection $mediaSelection,
     * itemId
     */
    public function __construct(array $params){
        //reference passed to the decorator strategy
        $this->processDetailsStrategy = $params['processDetailsStrategy'];
        $this->em = $params['em'];
        /*$this->em = $params['em'];
        $this->mediaSelection = $params['mediaSelection'];
        $this->apiStrategy = $params['apiStrategy'];
        $this->itemId = $params['itemId'];*/
        parent::__construct($params);
        $this->mediaResource = null; 
    }
    
    public function getAPIData(){
        return $this->processDetailsStrategy->getAPIData();
    }
    
    public function processMedia(){
        $this->mediaResource = $this->getMediaResource();
        $recommendations = $this->getRecommendations($this->mediaResource->getId());
        //process all the resources, which filters mr's based on uncached ones, then does a batch job
        parent::$mediaResources = array_merge(
                array($this->mediaResource->getId() => $this->mediaResource),
                $recommendations['genericMatches'],
                $recommendations['exactMatches']);
        
        parent::processMedia();
        $this->mediaResource->setRelatedMediaResources($recommendations);
        
        //return $this->mediaResource;
    }
    
    public function cacheMedia(){
        parent::cacheMedia();
    }
    
    public function getMedia(){
        if(is_null($this->mediaResource))
            throw new \RuntimeException("MediaResource is null");
            
        return $this->mediaResource;
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

    public function getMediaResource(){
        return $this->processDetailsStrategy->getMediaResource();
    }
    
    public function persistMergeMediaResource($mediaResource){
        parent::persistMergeMediaResource($mediaResource);
    }
    
    
}


?>
