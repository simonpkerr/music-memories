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
    protected $container;
    
    private $public_key;                           
        
    //uk private key
    private $private_key;
    
    private $associate_tag;
    
            
    public function getRequest(array $params){
        //$this->params = parent::parameters;
        //$this->container = parent::container;
        //$this->public_key = parent::container->getParameter('amazon_public_key');
        $this->public_key = parent::parameters;
        return null;
    }
}
    


?>
