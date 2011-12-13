<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * Base class for all media api's
 * @author Simon Kerr
 * @version 1.0
 */

namespace ThinkBack\MediaBundle\Resources\MediaAPI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use \Exception;

class MediaAPI implements IMediaAPI{
    protected $parameters;

    public function getRequest(array $params = null){}
    
    public function __construct($container = null){
        if($container !=null){
            $this->parameters = $container->parameters;
        }
            
    }
    
}


?>
