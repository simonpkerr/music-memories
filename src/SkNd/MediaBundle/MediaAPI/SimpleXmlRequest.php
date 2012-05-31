<?php
namespace SkNd\MediaBundle\MediaAPI;

function getSimpleXmlResponse($request){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $request);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
    $xml_response = curl_exec($ch);

    if($xml_response === False)
        return false;
    else{
        $parsed_xml = @simplexml_load_string($xml_response);
        return ($parsed_xml === False) ? False : $parsed_xml;
    }
}

?>

