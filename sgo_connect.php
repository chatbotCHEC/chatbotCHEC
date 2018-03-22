<?php

$url = "https://webservicedes.chec.com.co/WCF_Indisponibilidad/ServiceIndisponibilidad.svc?wsdl";
$soapClientOptions = array(
    'soap_version' => SOAP_1_1,
    'exceptions' => true,
    'trace' => 1,
    'cache_wsdl' => WSDL_CACHE_NONE,
);
$client = new SoapClient($url);

$niu = "101101442";
$result = $client->ConsultarIndisponibilidad($niu);

echo $result;
