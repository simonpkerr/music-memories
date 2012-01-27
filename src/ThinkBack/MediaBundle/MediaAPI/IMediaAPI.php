<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 *
 * Interface for connecting to and loading data from API's
 * 7Digital, Amazon, YouTube and Flickr.
 * @author Simon Kerr
 * @version 1.0
 */

namespace ThinkBack\MediaBundle\MediaAPI;

interface IMediaAPI {
    //public function __construct(array $params);
    public function getRequest(array $params);
    public function formatSearchString(array $params);
    //public function formatData();
    //public function showData();
    //public function getHost();
    //public function setTags(array $tags);
    
}

?>
