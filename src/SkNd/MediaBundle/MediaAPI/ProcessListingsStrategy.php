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
            throw new \RuntimeException('required params not supplied for '. get_class($this));
        
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
           
        //when getting cached listings, get the xml file and store in the listings object as xmlData 
        $this->listings = $this->em->getRepository('SkNdMediaBundle:MediaResourceListingsCache')->getCachedListings($this->mediaSelection);
        if(is_null($this->listings) || $this->listings->getLastModified()->format("Y-m-d H:i:s") < $this->apiStrategy->getValidCreationTime()){
            $this->listings = $this->createListings($this->apiStrategy->getListings($this->mediaSelection), $this->listings);
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
            $f = MediaAPI::CACHE_PATH . $listings->getXmlRef() . '.xml';
            if(file_exists($f)){
                try 
                {
                    unlink($f);
                } catch(\Exception $e){
                    throw new \Exception("error deleting old cached file");
                }
            }
            //$listings->setXmlRef($this->createXmlRef($xmlData));
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
        $listings->setXmlRef($this->createXmlRef($xmlData, $this->apiStrategy->getName()));
        
        return $listings;
    }
    
    public function convertMedia(){
        $date = $this->apiStrategy->getValidCreationTime();
        $listingsCollection = $this->em->createQuery('select c from SkNd\MediaBundle\Entity\MediaResourceListingsCache c where c.api = :api AND c.xmlRef IS NULL')
                ->setParameter('api', $this->apiStrategy->getAPIEntity())
                ->setMaxResults(2000)
                ->getResult();
       
        foreach ($listingsCollection as $listings){
            if($listings->getLastModified()->format("Y-m-d H:i:s") > $date){
                $listings->setXmlRef($this->createXmlRef($listings->getXmlData()));
                $listings->setXmlData(null);
                $this->em->persist($listings);
            } else {
                $this->em->remove($listings);
            }
            
        }
        $this->em->flush();
    }
    
    public function createXmlRef(SimpleXMLElement $xmlData, $apiKey){
        //create the xml file and create a reference to it
        $apiRef = substr($apiKey,0,1);
        $timeStamp = new \DateTime("now");
        $timeStamp = $timeStamp->format("Y-m-d_H-i-s");
        $xmlRef = uniqid('l' . $apiRef . '-' . $timeStamp);
        $xmlData->asXML(MediaAPI::CACHE_PATH . $xmlRef . '.xml');
        
        return $xmlRef;
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
