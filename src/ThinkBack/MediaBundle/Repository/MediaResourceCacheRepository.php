<?php

namespace ThinkBack\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaResourceCache is the associated record for MediaResource that saves the actual data 
 * and needs to be deleted after 24 hours in the case of amazon
 * @author Simon Kerr
 * @version 1.0
 */
class MediaResourceCacheRepository extends EntityRepository
{
}