<?php

/**
 * @abstract MediaDetailsRecommendationDecorator takes a MediaDetails object as a constructor param 
 * and decorates its functionality by looking up recommendations
 * @copyright Simon Kerr 2012
 * @author Simon Kerr
 */
namespace SkNd\MediaBundle\MediaAPI;

use Doctrine\ORM\EntityManager;
use SkNd\MediaBundle\MediaAPI\ProcessBatchStrategy;
use SkNd\MediaBundle\Entity\MediaResource;

class ProcessDetailsDecoratorStrategy extends ProcessBatchStrategy implements IProcessMediaStrategy, IMediaDetails {
    protected $processDetailsStrategy;
    protected $mediaResource;
    protected $em;
    //private $xmlFileManager;
    
    /**
     * @param array $params includes 
     * EntityManager, processDetailsStrategy, apis (to go to parent)
     */
    public function __construct(array $params){
        if(!isset($params['processDetailsStrategy'])||
           !isset($params['em']))
                throw new \RuntimeException('invalid params for ' . get_class($this));
        
        if(!$params['processDetailsStrategy'] instanceof IProcessMediaStrategy)
            throw new \RuntimeException('invalid details strategy');
        
        if(!$params['em'] instanceof EntityManager)
            throw new \RuntimeException('invalid em');
        
        $this->processDetailsStrategy = $params['processDetailsStrategy'];
        $this->em = $params['em'];
        
        if(isset($params['xmlFileManager']) && $params['xmlFileManager'] instanceof XMLFileManager){
            $this->setXMLFileManager($params['xmlFileManager']);
        }  
        
        parent::__construct($params);
    }
    
    public function setXMLFileManager(XMLFileManager $xmlFileManager) {
        $this->processDetailsStrategy->setXMLFileManager($xmlFileManager);
        parent::setXMLFileManager($xmlFileManager);
    }
    
    public function getXMLFileManager() {
        return parent::getXMLFileManager();
    }
    
    public function getMediaSelection(){
        return $this->processDetailsStrategy->getMediaSelection();
    }
    
    public function getAPIData(){
        return $this->processDetailsStrategy->getAPIData();
    }
    
    public function processMedia(){
        $this->mediaResource = $this->getMediaResource();
        $recommendations = $this->getRecommendations($this->mediaResource);
        //process all the resources, which filters mr's based on uncached ones, then does a batch job
        $this->mediaResources = array_merge(
                array($this->mediaResource->getId() => $this->mediaResource),
                $recommendations['genericMatches'],
                $recommendations['exactMatches']);
        
        parent::processMedia();
        
        if(count($recommendations['genericMatches']) > 0)
            $this->mediaResource->setRelatedMediaResources($recommendations);
        
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
    protected function getRecommendations(MediaResource $mr) {
        $recommendationSet = $this->em->getRepository('SkNdMediaBundle:MediaResource')->getMediaResourceRecommendations($mr);
        return $recommendationSet;
    }

    public function getMediaResource(){
        return $this->processDetailsStrategy->getMediaResource();
    }
    
    public function persistMergeFlush($obj = null, $immediateFlush = true){
        parent::persistMergeFlush($obj, $immediateFlush);
    }
    
    
    
    
}


?>
