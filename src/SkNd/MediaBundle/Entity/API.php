<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * API is the entity for setting and getting api attributes.
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Entity;

class API
{
    public static $default = 'amazonapi';
    protected $id;
    protected $name;
    protected $host;
    protected $friendlyName;

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function setFriendlyName($name)
    {
        $this->friendlyName = $name;
    }

    public function getFriendlyName()
    {
        return $this->friendlyName;
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function getHost()
    {
        return $this->host;
    }
}