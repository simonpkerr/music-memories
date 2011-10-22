<?php

namespace ThinkBack\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ThinkBackUserBundle extends Bundle
{
    public function getParent(){
        return 'FOSUserBundle';
    }
}
