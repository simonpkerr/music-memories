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
use SkNd\MediaBundle\Entity\MediaResourceListingsCache;
use \SimpleXMLElement;
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\MediaAPI\XMLFileManager;

class ProcessListingsStrategy implements IProcessMediaStrategy {
    protected $apiStrategy;
    protected $em;
    protected $mediaSelection;
    protected $listings;
    protected $recommendations;
    protected $utilities;
    protected $xmlFileManager;

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
            throw new \RuntimeException('required params not supplied for '. get_class($this));
        
        if(isset($params['xmlFileManager']) && $params['xmlFileManager'] instanceof XMLFileManager){
            $this->xmlFileManager = $params['xmlFileManager'];
        }        
        $this->em = $params['em'];
        $this->mediaSelection = $params['mediaSelection'];
        $this->apiStrategy = $params['apiStrategy'];
        $this->utilities = new Utilities();
    }
    
    public function setXMLFileManager(XMLFileManager $xmlFileManager) {
        $this->xmlFileManager = $xmlFileManager;
    }
    
    public function getXMLFileManager(){
        if(is_null($this->xmlFileManager))
            throw new \RuntimeException("xml file manager has not been set for " . get_class($this));
        
        return $this->xmlFileManager;
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
           
        //when getting cached listings, get the xml file and store in the listings object as xmlData 
        $this->listings = $this->em->getRepository('SkNdMediaBundle:MediaResourceListingsCache')->getCachedListings($this->mediaSelection);
        if(is_null($this->listings) || $this->listings->getLastModified()->format("Y-m-d H:i:s") < $this->apiStrategy->getValidCreationTime() || !$this->getXMLFileManager()->xmlRefExists($this->listings->getXmlRef())){
            $this->listings = $this->createListings($this->apiStrategy->getListings($this->mediaSelection), $this->listings);
        } else {
            $this->listings->setXmlData($this->getXMLFileManager()->getXmlData($this->listings->getXmlRef()));
        }
        
        $this->recommendations = $this->getRecommendations();
      
    }
    
    public function getRecommendations() {
        if($this->mediaSelection->getDecade() != null){
            $recommendations = $this->em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallsByDecade($this->mediaSelection->getDecade());
            if(count($recommendations) > 0)
                return $recommendations;
        }
       
        return null;
        
    }
    
    private function createListings(SimpleXMLElement $xmlData, MediaResourceListingsCache $listings = null){
        //if listings object exists but cache is out of date
        if(!is_null($listings)){
            $this->getXMLFileManager()->deleteXmlData($listings->getXmlRef());
          
        } else {
            $listings = new MediaResourceListingsCache();
            $listings->setAPI($this->mediaSelection->getAPI());
            $listings->setMediaType($this->mediaSelection->getMediaType());
            $listings->setDecade($this->mediaSelection->getDecade());
            $listings->setGenre($this->mediaSelection->getSelectedMediaGenre());
            $listings->setKeywords($this->mediaSelection->getKeywords());
            $listings->setComputedKeywords($this->mediaSelection->getComputedKeywords());
            $listings->setPage($this->mediaSelection->getPage() != 1 ? $this->mediaSelection->getPage() : null);
                        
        }
        $listings->setXmlRef($this->getXMLFileManager()->createXmlRef($xmlData, $this->apiStrategy->getName()));
        $listings->setXmlData($this->getXMLFileManager()->getXmlData($listings->getXmlRef()));
        
        return $listings;
    }
    
    //will be deprecated
    public function convertMedia(){
        $date = $this->apiStrategy->getValidCreationTime();
        $listingsCollection = $this->em->createQuery('select c from SkNd\MediaBundle\Entity\MediaResourceListingsCache c where c.api = :api AND c.xmlRef IS NULL')
                ->setParameter('api', $this->apiStrategy->getAPIEntity())
                ->setMaxResults(2000)
                ->getResult();
       
        foreach ($listingsCollection as $listings){
            if($listings->getLastModified()->format("Y-m-d H:i:s") > $date){
                $listings->setXmlRef($this->getXMLFileManager()->createXmlRef($listings->getXmlData(), $this->apiStrategy->getName()));
                $listings->setXmlData(null);
                $this->em->persist($listings);
            } else {
                $this->em->remove($listings);
            }
            
        }
        $this->em->flush();
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
