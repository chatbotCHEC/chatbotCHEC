<?php
$client = new SoapClient('https://webservicedes.chec.com.co/WCF_Indisponibilidad/ServiceIndisponibilidad.svc?wsdl', array('soap_version' => SOAP_1_2));

var_dump($client->__getFunctions());
$niu = "182894608";
$result = $client->__call(ConsultarIndisponibilidad($niu));

echo "<pre>";
print_r($result);
echo "</pre>";

?>