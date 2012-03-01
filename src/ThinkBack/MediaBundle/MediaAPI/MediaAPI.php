<?php
namespace ThinkBack\MediaBundle\MediaAPI;

class MediaAPI {
    
    private $apiStrategy;
    private $apis;
    private $debugMode = false;
    
    /*
     * not sure how to implement debug_mode yet
     * so that none of the apis in the array call 
     * a live api
     */
    public function __construct($debug_mode, array $apis){
        $this->apis = $apis;
        $this->debugMode = $debug_mode;
        $this->setAPIStrategy('amazonapi');
    }
    
    public function getAPIs(){
        return $this->apis;
    }
    
    public function getRequest(array $params){
        return $this->apiStrategy->getRequest($params);
    }
    
    public function setAPIStrategy($apiStrategyKey){
        if(isset($this->apis[$apiStrategyKey]))
            $this->apiStrategy = $this->apis[$apiStrategyKey];
        else
            throw new \RuntimeException("api key not found");
    }
    
    public function cacheListings(){
        //todo
    }
    
    public function getRecommendations(array $params){
        //todo
        
    }
    
    public function setRecommendation(array $params){
        //todo
    }
    
    
    
    
}
?>
