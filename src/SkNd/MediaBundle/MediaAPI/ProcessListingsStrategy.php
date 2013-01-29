<?php

/**
 * @abstract ProcessListingsStrategy
 * @copyright Simon Kerr 2012
 * @author Simon Kerr
 * @uses MediaController
 * @version 1.0
 */
namespace SkNd\MediaBundle\MediaAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use Doctrine\ORM\EntityManager;
use SkNd\MediaBundle\MediaAPI\IAPIStrategy;
use SkNd\MediaBundle\MediaAPI\MediaDetails;
use SkNd\MediaBundle\Entity\MediaResourceListingsCache;
use \SimpleXMLElement;
use SkNd\MediaBundle\MediaAPI\Utilities;

class ProcessListingsStrategy implements IProcessMediaStrategy {
    protected $apiStrategy;
    protected $em;
    protected $mediaSelection;
    protected $listings;
    protected $recommendations;
    protected $utilities;

    /**
     * @param array $params includes -
     * @param EntityManager $em, 
     * @param array $apiStrategy,
     * @param MediaSelection $mediaSelection 
     */
    public function __construct(array $params){
        if(!isset($params['em'])||
                !isset($params['mediaSelection'])||
                !isset($params['apiStrategy']))
            throw new \RuntimeException('required params not supplied for '. $this);
        
        $this->em = $params['em'];
        $this->mediaSelection = $params['mediaSelection'];
        $this->apiStrategy = $params['apiStrategy'];
        $this->utilities = new Utilities();
    }
    
    //is this needed? its no longer referenced in mediaapi
    public function getAPIData(){
        return $this->apiStrategy;
    }
    
    public function getMedia(){ 
        if(is_null($this->listings))
            throw new \RuntimeException("listings are null");
            
        return array(
            'listings'          => $this->listings,
            'recommendations'   => $this->recommendations,
        );
    }

    public function processMedia(){
        $this->listings = null;
           
        $this->listings = $this->em->getRepository('SkNdMediaBundle:MediaResourceListingsCache')->getCachedListings($this->mediaSelection);
        if(is_null($this->listings) || $this->listings->getLastModified()->format("Y-m-d H:i:s") < $this->apiStrategy->getValidCreationTime()){
            $this->listings = $this->createCachedListings($this->apiStrategy->getListings($this->mediaSelection), $this->listings);
        }
        
        $this->recommendations = $this->getRecommendations();
      
    }
    
    public function getRecommendations() {
        $recommendations = null;
   
        if($this->mediaSelection->getDecade() != null){
            $recommendations = $this->em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallsByDecade($this->mediaSelection->getDecade());
            if(count($recommendations) > 0)
                return $recommendations;
        }
       
        return null;
        
    }
    
    //do something here about integrating the API classes and entities
    private function createCachedListings(SimpleXMLElement $xmlData, MediaResourceListingsCache $listings = null){
        
        if(!is_null($listings)){
            $listings->setXmlData($xmlData->asXML());      
        }else{
            $listings = new MediaResourceListingsCache();
            //$listings->setAPI($this->em->getRepository('SkNdMediaBundle:API')->getAPIByName($this->apiStrategy->getName()));
            //THE API ENTITY IS NOW IN THE APISTRATEGY CLASS
            //$listings->setAPI($this->apiStrategy->getAPIEntity());
            $listings->setAPI($this->mediaSelection->getAPI());
            $listings->setMediaType($this->mediaSelection->getMediaType());
            $listings->setDecade($this->mediaSelection->getDecade());
            $listings->setGenre($this->mediaSelection->getSelectedMediaGenre());
            $listings->setKeywords($this->mediaSelection->getKeywords());
            $listings->setComputedKeywords($this->mediaSelection->getComputedKeywords());
            $listings->setPage($this->mediaSelection->getPage() != 1 ? $this->mediaSelection->getPage() : null);
            $listings->setXmlData($xmlData->asXML());
        }
        
        return $listings;
    }
    
    //check date created first, then either replace xmldata and re-cache or do nothing.
    public function cacheMedia(){ 
        $this->persistMergeFlush($this->listings);
    }
    
    public function persistMergeFlush($obj = null, $immediateFlush = true){
        $this->utilities->persistMergeFlush($this->em, $obj, $immediateFlush);
    }
}


?>
