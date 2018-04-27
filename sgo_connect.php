<?php
require "Mesmotronic/Soap/WsaSoap.php";
require "Mesmotronic/Soap/WsaSoapClient.php";
require "Mesmotronic/Soap/WsseAuthHeader.php";

error_reporting(-1);
ini_set('display_errors', 'On');

function consultarIndisponibilidad($niu){
    
    $wsdl = "https://checindisponibilidaddes.chec.com.co/ServiceIndisponibilidad.svc?wsdl";
    
    $client = new \Mesmotronic\Soap\WsaSoapClient($wsdl);
    $result = $client->ConsultarIndisponibilidad(array('Cuenta' => $niu));
    
    return $result->ConsultarIndisponibilidadResult;
}

function consularInterrupcionesDelServicioXCuenta($niu){
    
    $wsdl = "https://checindisponibilidaddes.chec.com.co/ServiceIndisponibilidad.svc?wsdl";
    
    $client2 = new \Mesmotronic\Soap\WsaSoapClient($wsdl);
    $result2 = $client2->consularInterrupcionesDelServicioXCuenta(array('Cuenta' => $niu));
    
    return $result2->consularInterrupcionesDelServicioXCuentaResult;
}

function consultarIndisponibilidadXNodo($nodo){
    
    $wsdl = "https://checindisponibilidaddes.chec.com.co/ServiceIndisponibilidad.svc?wsdl";
    
    $client3 = new \Mesmotronic\Soap\WsaSoapClient($wsdl);
    $result3 = $client3->consultarIndisponibilidadXNodo(array('Nodo' => $nodo));
    
    return $result3->consultarIndisponibilidadXNodoResult;
}

//Aca estoy intentando hacer la validación para que cuando la cabecera ingresemos un GET[NIU] solo muestre esas peticiones ósea los dos primeros métodos

//SINO, que entre al último método que es la petición get.NODO y muestre solo el último var_dump
if( consultarIndisponibilidad($_GET['niu']) == true  && consularInterrupcionesDelServicioXCuenta($_GET['niu']) == true){

echo "Indisponibilidades:";
echo "<br>";

var_dump(consultarIndisponibilidad($_GET['niu']));
echo "<br>";

echo "<br>";
echo "Interrupciones del Servicio:";
echo "<br>";

var_dump(consularInterrupcionesDelServicioXCuenta($_GET['niu']));
echo "</br>";

}else{
echo "<br>";
echo "Indisponibilidades por Nodo:";
echo "<br>";

var_dump(consultarIndisponibilidadXNodo($_GET['nodo']));
echo "</br>";
}


?>