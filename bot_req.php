<?php

error_reporting(-1);
ini_set('display_errors', 'On');

//TODO: consultas.php 1

require './lib.php';

//Instancia de la API
$api = new chatBotApi();
$Nombre = "";
$Telefono = "";
$Direccion = "";
$number = "";

//Almacena los contextos de la petición
$contexts = array();

//Obtener el cuerpo de la petición que viene de API.ai
$reqBody = $api->detectRequestBody();
var_dump($reqBody);
//Obtener los contextos de la petición
foreach ($reqBody['result']['contexts'] as $valor) {
    array_push($contexts, $valor);
}

//Verifica si de la petición se recibe la entidad number
if (isset($reqBody['result']['parameters']['number'])) {
    $number = strval($reqBody['result']['parameters']['number']);
}

//Verifica si de la petición se recibe la entidad nombre
if (isset($reqBody['result']['parameters']['given-name'])) {
    $Nombre = $reqBody['result']['parameters']['given-name'] . " " . $reqBody['result']['parameters']['last-name'];
}

//Verifica si de la petición se recibe el municipio
if(isset($reqBody['result']['parameters']['municipio'])){
    $municipio = $reqBody['result']['parameters']['municipio'];
}elseif (isset($contexts[0]['parameters']['municipio'])) {
    $municipio = $contexts[0]['parameters']['municipio'];
}else{
    $municipio = '';
}


//Switch que determina cuál es el contexto principal de la petición y ejecuta una función del objeto api correspondientemente.
switch ($contexts[0]['name']) {
    case 'c1_cc':
        $response = $api->getIndisCC($number);
        break;
    case 'c1_direccion_municipio':
        $direccion = $reqBody['result']['resolvedQuery'];
        $response = $api->getIndisAddress($direccion, $municipio);
        break;
    case 'c1_nit':
        $response = $api->getIndisNIT($number);
        break;
    case 'c1_niu':
        //medida de control ante error en contextOut CORREGIR
        $response = $api->getIndisNiu($number);
        break;
    case 'c1_nombre_municipio':
        $nombre = $reqBody['result']['resolvedQuery'];
        $response = $api->getIndisNombre($nombre, $municipio);
        break;
    case 'c2_cc':
        $response = $api->getSPCC($number);
        break;
    case 'c2_direccion_municipio':
        $direccion = $reqBody['result']['resolvedQuery'];
        $response = $api->getSPAddress($direccion, $municipio);
        break;
    case 'c2_nit':
        $response = $api->getSPNIT($number);
        break;
    case 'c2_niu':
        $response = $api->getSPNiu($number);
        break;
        
    case 'c2_nombre_municipio':
        $nombre = $reqBody['result']['resolvedQuery'];
        $response = $api->getSPNombre($nombre, $municipio);
        break;
}


header("Content-Type: application/json");
echo json_encode($response);
