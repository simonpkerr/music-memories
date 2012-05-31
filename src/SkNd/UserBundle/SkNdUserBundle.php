<?php

namespace SkNd\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SkNdUserBundle extends Bundle
{
    public function getParent(){
        return 'FOSUserBundle';
    }
}
