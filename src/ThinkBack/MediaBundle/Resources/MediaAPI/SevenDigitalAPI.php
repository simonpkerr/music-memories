<?php
namespace ThinkBack\MediaBundle\Resources\MediaAPI;
require_once 'SimpleXmlRequest.php';

class SevenDigitalAPI implements IMediaAPI{
    protected $method = "release/bytag/top";
    protected $host = "http://api.7digital.com/1.2/";
    protected $page = 1;
    protected $pageSize = 50;
    protected $apiKey = "YOUR_KEY_HERE";
    //protected $decade;
    //protected $genre;
    protected $tags;
    
    /*
     * get the request by passing genre and decade tags to 7Digital
     * which results in a simplexml response which can be passed to the template
     */
    public function getRequest(array $params){
        //$this->tags = $params['tags'];
        ksort($params);
        //$canonicalized_query = array();

        /*foreach ($params as $param=>$value)
        {
            $param = str_replace("%7E", "~", rawurlencode($param));
            $value = str_replace("%7E", "~", rawurlencode($value));
            $canonicalized_query[] = $param."=".$value;
        }*/
        //$canonicalized_query = implode(",", $canonicalized_query);
        $params = implode(",", $params);

        $request = $this->host . $this->method . "?tags=" . $params . "&page=". $this->page . "&pageSize=" . $this->pageSize . "&oauth_consumer_key=" . $this->apiKey;
        return getSimpleXmlResponse($request);    
    }
    
   // public function setTags(array $tags){
        //$this->decade = $tags['decade'];
        //$this->genre = $tags['genre'];
       // $this->tags = $tags;
    //}
}


?>
