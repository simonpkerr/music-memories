<?php
namespace ThinkBack\MediaBundle\MediaAPI;
use ThinkBack\MediaBundle\MediaAPI\AmazonSignedRequest;
use Symfony\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use ThinkBack\MediaBundle\Entity\Decade;
use ThinkBack\MediaBundle\Entity\Genre;
use ThinkBack\MediaBundle\Entity\MediaType;
use ThinkBack\MediaBundle\Entity\MediaSelection;
use ThinkBack\MediaBundle\MediaAPI\Utilities;

class AmazonAPI implements IAPIStrategy {
    public $API_NAME = 'amazonapi';
    private $amazonParameters;
    private $public_key;                           
    private $private_key;
    private $associate_tag;
    protected $asr;
    //private $doctrine;
    //private $em;
    
    public function __construct(array $access_params, $amazon_signed_request){//, Registry $doctrine){
            
        $this->public_key = $access_params['amazon_public_key'];
        $this->private_key = $access_params['amazon_private_key'];
        $this->associate_tag = $access_params['amazon_associate_tag'];
        
        $this->asr = $amazon_signed_request; 
        
        //$this->doctrine = $doctrine;
        //$this->em = $doctrine->getEntityManager();
        
        $this->amazonParameters = array(
                "Operation"     => "ItemSearch",
                //"ResponseGroup" => "ItemAttributes,SalesRank,Similarities,Request",
                "ResponseGroup" => "Images,ItemAttributes,SalesRank,Request",
                "Condition"     => "All",
                //"ProductGroup"  => "Music",
                "MerchantId"    => "All",
                //"Format"        => "VHS",
                "ItemPage"      => "1",
                //"Sort"          => "salesrank",
                //"Sort"          => "-releasedate", // release date oldest to newest
                "Validate"      => "True",
         );
    }
    
    public function getName(){
        return $this->API_NAME;
    }
    
    public function setAmazonSignedRequest($asr){
        $this->asr = $asr;
    }
    
    public function getListings(MediaSelection $mediaSelection){
        $browseNodeArray = array(); 
            
        array_push($browseNodeArray, $mediaSelection->getMediaTypes()->getAmazonBrowseNodeId());
        
        if($mediaSelection->getDecades() != null)
            array_push($browseNodeArray, $mediaSelection->getDecades()->getAmazonBrowseNodeId());

        if($mediaSelection->getSelectedMediaGenres() != null)
            array_push($browseNodeArray, $mediaSelection->getSelectedMediaGenres()->getAmazonBrowseNodeId());
            
        $canonicalBrowseNodes = implode(',', $browseNodeArray);

        $params = Utilities::removeNullEntries(array(
            'Keywords'       =>      $mediaSelection->getKeywords() != null ? $mediaSelection->getKeywords() : null,
            'BrowseNode'     =>      $canonicalBrowseNodes,
            'SearchIndex'    =>      'Video',
            'ItemPage'       =>      $mediaSelection->getPage(),
            'Sort'           =>      'salesrank',
        ));
        
        if(array_key_exists('ItemPage', $params) && $params['ItemPage'] > 10){
            throw new \RunTimeException("Requested page was out of bounds");
        }
        
        $this->amazonParameters = array_merge($this->amazonParameters, $params);
        //need to account for page number, sort type, number of results
        
        $xml_response = $this->queryAmazon($this->amazonParameters, "co.uk");
        
        try{
            return $this->verifyXmlResponse($xml_response);
        }catch(\RunTimeException $re){
            throw $re;
        }catch(\LengthException $le){
            throw $le;
        }
    }
    
    /*
     * getDetails handles calls to the live api, calls to the db to get recommendations
     * and then save a details product back to the db as a MediaResource to drive recommendations
     * @param searchParams - all the media search parameters 
     * @param params - params to carry out the query - only contains the id of the amazon product
     */
    public function getDetails(array $params, array $searchParams){
        //look up the detail in the db to see if its cached
        
        //get the recommendations
        
        //look up the details from the api if not cached
        $this->amazonParameters = array_merge($params, array(
               'Operation'          =>      'ItemLookup',
               'ResponseGroup'      =>      'Images,ItemAttributes,SalesRank,Request,Similarities',
 
        ));

        $this->amazonParameters = array_merge($this->amazonParameters, $params);
        $xml_response = $this->queryAmazon($this->amazonParameters, "co.uk");
        
        try{
            return $this->verifyXmlResponse($xml_response);
        }catch(\RunTimeException $re){
            throw $re;
        }catch(\LengthException $le){
            throw $le;
        }
        
        //store the recommendation in cache
        
        
    }
    
    /**
     * Check if the xml received from Amazon is valid
     * 
     * @param mixed $response xml response to check
     * @return bool false if the xml is invalid
     * @return mixed the xml response if it is valid
     * @return exception if we could not connect to Amazon
     */
    protected function verifyXmlResponse($response)
    {
        if ($response === False)
        {
            throw new \RuntimeException("Could not connect to Amazon");
        }
        else
        {
            /*previous class used below statement, but modified looks at total results returned
            if (isset($response->Items->Item->ItemAttributes->Title))
            */

            if($response->Items->TotalResults > 0 || $this->amazonParameters['Operation'] == 'ItemLookup')
                return ($response);
            else
                throw new \LengthException("No results were returned");
            
            return $response;
        }
    }
    
    /**
     * Query Amazon with the issued parameters
     * 
     * @param array $parameters parameters to query around
     * @return simpleXmlObject xml query response
     */
    protected function queryAmazon($parameters, $region = "com")
    {
        return $this->asr->aws_signed_request($region, $parameters, $this->public_key, $this->private_key, $this->associate_tag);
    }
    
}


?>
