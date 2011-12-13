<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * Connects to Amazon API to return TV and films
 * @author Simon Kerr
 * @version 1.0
 */

namespace ThinkBack\MediaBundle\Resources\MediaAPI;
require_once 'aws_signed_request.php';


class AmazonAPI extends MediaAPI {
    private $amazonParameters;
    
    private $public_key;                           
        
    private $private_key;
    
    private $associate_tag;
    
    public function __construct($container = null){
        parent::__construct($container);
        $this->public_key = $this->parameters['amazon_public_key'];
        $this->private_key = $this->parameters['amazon_uk_private_key'];
        $this->associate_tag = $this->parameters['amazon_associate_tag'];
        $this->amazonParameters = array(
            "Operation"     => "ItemSearch",
            //"ResponseGroup" => "ItemAttributes,SalesRank,Similarities,Request",
            "ResponseGroup" => "Images,ItemAttributes,SalesRank,Request",
            "Condition"     => "All",
            //"ProductGroup"  => "Music",
            "MerchantId"    => "All",
            //"Format"        => "VHS",
            "ItemPage"      => "1",
            "Sort"          => "salesrank",
            //"Sort"          => "-releasedate", // release date oldest to newest
            "Validate"      => "True",
        );
    }
            
    public function getRequest(array $params){
        $this->amazonParameters = array_merge($this->amazonParameters, $params);
        //need to account for page number, sort type, number of results
        
        $xml_response = $this->queryAmazon($this->amazonParameters, "co.uk");
        
        return $this->verifyXmlResponse($xml_response);
        
    }
    
    /**
     * Check if the xml received from Amazon is valid
     * 
     * @param mixed $response xml response to check
     * @return bool false if the xml is invalid
     * @return mixed the xml response if it is valid
     * @return exception if we could not connect to Amazon
     */
    private function verifyXmlResponse($response)
    {
        if ($response === False)
        {
            throw new Exception("Could not connect to Amazon");
        }
        else
        {
            /*previous class used below statement, but modified looks at total results returned
            if (isset($response->Items->Item->ItemAttributes->Title))
            */

            if($response->Items->TotalResults > 0)
                return ($response);
            else
                throw new Exception("no results returned");
            
            return $response;
        }
    }
    
    /**
     * Query Amazon with the issued parameters
     * 
     * @param array $parameters parameters to query around
     * @return simpleXmlObject xml query response
     */
    private function queryAmazon($parameters, $region = "com")
    {
        return aws_signed_request($region, $parameters, $this->public_key, $this->private_key, $this->associate_tag);
    }
    
}
    


?>
