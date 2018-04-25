<?php

error_reporting(-1);
ini_set('display_errors', 'On');


var_dump(consultarIndisponibilidad($_GET['niu']));

function consultarIndisponibilidad($niu){
    require "Mesmotronic/Soap/WsaSoap.php";
    require "Mesmotronic/Soap/WsaSoapClient.php";
    require "Mesmotronic/Soap/WsseAuthHeader.php";
    
    $wsdl = "https://webservicedes.chec.com.co/WCF_Indisponibilidad/ServiceIndisponibilidad.svc?wsdl";
    
    $client = new \Mesmotronic\Soap\WsaSoapClient($wsdl);
    $result = $client->ConsultarIndisponibilidad(array('Cuenta' => $niu));
    
    return $result->ConsultarIndisponibilidadResult;
}

?>