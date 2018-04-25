<?php
 try { 
    $options = array( 
        'soap_version'=>SOAP_1_2, 
        'exceptions'=>true, 
        'trace'=>1, 
        'cache_wsdl'=>WSDL_CACHE_NONE 
    ); 
    $client = new SoapClient('https://webservicedes.chec.com.co/WCF_Indisponibilidad/ServiceIndisponibilidad.svc?wsdl', $options); 
// Note where 'Get' and 'request' tags are in the XML
$niu = "182894608"; 
    $results = $client->ConsultarIndisponibilidad($niu);
    echo $result;
} catch (Exception $e) { 
    echo "<h2>Exception Error!</h2>"; 
    echo $e->getMessage(); 
} 



?>