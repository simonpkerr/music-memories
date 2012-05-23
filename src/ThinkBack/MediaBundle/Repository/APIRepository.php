<?php

namespace ThinkBack\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * APIRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class APIRepository extends EntityRepository
{
    public function getAPIByName($name){
        return $this->findOneBy(array('name' => $name));
    }
    

}