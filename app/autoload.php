<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
//ini_set("include_path", ".:/usr/lib/php:/usr/local/lib/php:/home/simonker/public_html/SkNd/app/Resources/ZendFramework/library");
//ini_set("memory_limit","32M");

$loader = require __DIR__.'/../vendor/autoload.php';

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';

    $loader->add('', __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs');
}

//$loader->registerPrefixFallbacks(array_merge(explode(';',get_include_path())));
//the zend framework has been added to the include path in the php.ini file
//$loader->registerPrefixFallbacks(array(__DIR__.'/Resources/ZendFramework/library'));
require_once 'Zend/Loader.php';
\Zend_Loader::loadClass('Zend_Gdata_YouTube');

//require_once 'FirePHPCore/FirePHP.class.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
