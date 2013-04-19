<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
//ini_set("include_path", ".:/usr/lib/php:/usr/local/lib/php:/home/simonker/public_html/SkNd/app/Resources/ZendFramework/library");

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('Application', __DIR__);

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';

    $loader->add('', __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs');
}

require_once 'Zend/Loader.php';
\Zend_Loader::loadClass('Zend_Gdata_YouTube');

//require_once 'FirePHPCore/FirePHP.class.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
