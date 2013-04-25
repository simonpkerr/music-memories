<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * AmazonAPI controls access to the live amazon api or the dummy amazon api 
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\MediaAPI;
use SkNd\MediaBundle\MediaAPI\AmazonSignedRequest;
use Symfony\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use SkNd\MediaBundle\Entity\Decade;
use SkNd\MediaBundle\Entity\Genre;
use SkNd\MediaBundle\Entity\MediaType;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\API;
use SkNd\MediaBundle\MediaAPI\Utilities;
use \SimpleXMLElement;


class AmazonAPI implements IAPIStrategy {
    const FRIENDLY_NAME = 'Amazon';
    const API_NAME = 'amazonapi';
    const BATCH_PROCESS_THRESHOLD = 10;
    protected $apiEntity;
    private $amazonParameters;
    private $public_key;                           
    private $private_key;
    private $associate_tag;
    protected $asr;
    private $ITEM_SEARCH = 'ItemSearch';
    private $ITEM_LOOKUP = 'ItemLookup';
 
    public function __construct(array $access_params, $amazon_signed_request){
            
        $this->public_key = $access_params['amazon_public_key'];
        $this->private_key = $access_params['amazon_private_key'];
        $this->associate_tag = $access_params['amazon_associate_tag'];
        
        $this->asr = $amazon_signed_request;     
        $this->amazonParameters = array(
                "Operation"     => $this->ITEM_SEARCH,
                "ResponseGroup" => "Images,Small,Request",
                "Condition"     => "All",
                "MerchantId"    => "All",
                "ItemPage"      => "1",
                //"Sort"          => "salesrank", //sorting functionality to be added later
                //"Sort"          => "-releasedate", // release date oldest to newest
                "Validate"      => "True",
         );
    }
    
    public function getName(){
        return self::API_NAME;
    }
    
    public function setAPIEntity(API $entity){
        $this->apiEntity = $entity;
    }
    
    public function getAPIEntity(){
        return $this->apiEntity;
    }
    
    public function setAmazonSignedRequest($asr){
        $this->asr = $asr;
    }
    
    //each api will have it's own method for returning the id of a mediaresource for caching purposes.
    public function getIdFromXML(SimpleXMLElement $xmlData){
        return (string)$xmlData->ASIN;
    }
    
    public function getXML(SimpleXMLElement $xmlData){
        return $xmlData->asXML();
    }
    
    
    public function getImageUrlFromXML(SimpleXMLElement $xmlData){
        try{
            return (string)$xmlData->MediumImage->URL;
        } catch(\RuntimeException $re){
            return null;
        }
    }
    
    public function getItemTitleFromXML(SimpleXMLElement $xmlData){
        try{
            return (string)$xmlData->ItemAttributes->Title;
        } catch(\RuntimeException $re){
            return null;
        }
    }
    
    public function getDecadeFromXML(SimpleXMLElement $xmlData) {
        try{
            $title = (string)$xmlData->ItemAttributes->Title;
            $yearParts = array();
            preg_match('/[\[|\(](\d{4})[\]|\)]/i', $title, $yearParts);
            $year = isset($yearParts[1]) ? $yearParts[1] : null;
            if(!is_null($year)){
                return substr($year, 0, 3) . '0s';
            }
            
            return null;
        }catch(\RuntimeException $re){
            return null;
        }
    }
    
    public function getListings(MediaSelection $mediaSelection){
        $browseNodeArray = array(); 
            
        array_push($browseNodeArray, $mediaSelection->getMediaType()->getAmazonBrowseNodeId());
        
        if($mediaSelection->getDecade() != null)
            array_push($browseNodeArray, $mediaSelection->getDecade()->getAmazonBrowseNodeId());

        if($mediaSelection->getSelectedMediaGenre() != null)
            array_push($browseNodeArray, $mediaSelection->getSelectedMediaGenre()->getAmazonBrowseNodeId());
            
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
            $xml_response = $this->verifyXmlResponse($xml_response)->Items;
        }catch(\RunTimeException $re){
            throw $re;
        }catch(\LengthException $le){
            throw $le;
        }
        
        return $xml_response;
    }
    
    /*
     * getDetails handles calls to the live api, 
     * @param params - params to carry out the query - only contains the id of the amazon product
     */
    public function getDetails(array $params){
        $this->amazonParameters = array_merge(
                $this->amazonParameters,
                $params, 
                array(
               'Operation'          =>     $this->ITEM_LOOKUP,
               //'ResponseGroup'      =>    'Images,ItemAttributes,Request,Similarities,EditorialReview',
               'ResponseGroup'      =>      'Images,ItemAttributes,Request,Similarities,OfferSummary',
                ));

        
        $xml_response = $this->queryAmazon($this->amazonParameters, "co.uk");
        
        try{
            $verifiedResponse = $this->verifyXmlResponse($xml_response);
        }catch(\RunTimeException $re){
            throw $re;
        }catch(\LengthException $le){
            throw $le;
        }
        
        //certain operations like batch processing only pass ids and do not require recommendations
        return $verifiedResponse->Items->Item;
        
    }
    
    /**
     * Performs a batch process of up to 10 ids to look up 
     * for memory walls
     * @param array $ids 
     * 
     */
    public function getBatch(array $ids){
        if(count($ids) > self::BATCH_PROCESS_THRESHOLD)
            $ids = array_slice ($ids, 0, self::BATCH_PROCESS_THRESHOLD);
            
        $params = array(
            'ItemId'  => implode(',', $ids),
        );
        return $this->getDetails($params);
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
            //for searches
            if($response->Items->TotalResults == 0 && $this->amazonParameters['Operation'] == $this->ITEM_SEARCH)
                throw new \LengthException("No results were returned");
            
            //for lookups
            if(!$response->Items->Request->IsValid && $this->amazonParameters['Operation'] == $this->ITEM_LOOKUP)
                throw new \RuntimeException("Invalid result set");
            
            if($response->Items->Request->Errors->Error != null) 
                throw new \RuntimeException($response->Items->Request->Errors->Error->Message);
            
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
    
    /**
     * returns a DateTime object against which records can be compared 
     * to determine whether cached records for amazon can be used or need
     * to be updated from the live api. According to toc for amazon, this
     * threshold is 24 hours.
     * @return type DateTime
     */
    public function getValidCreationTime(){
         $date = new \DateTime("now");
         $date = $date->sub(new \DateInterval('PT24H'))->format("Y-m-d H:i:s");

         return $date;
    }
}


?>
