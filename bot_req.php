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


/* $fp = fopen('request.json', 'w');
fwrite($fp, json_encode($reqBody));
fclose($fp); */


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


//flag para identificar si se hace un request diferente a niu y no sobreescribir la respuesta
$answered = false;

//Switch que determina cuál es el contexto principal de la petición y ejecuta una función del objeto api correspondientemente.
foreach ($contexts as $i => $con) {

    //Verifica si de la petición se recibe el municipio
    if(isset($reqBody['result']['parameters']['municipio'])){
        $raw_municipio = $reqBody['result']['parameters']['municipio'];
    }elseif (isset($con['parameters']['municipio'])) {
        $raw_municipio = $con['parameters']['municipio'];
    }else{
        $raw_municipio = '';
    }

    $municipio = strtoupper($raw_municipio);

    switch ($con['name']) {
        case 'c1_cc':
            $response = $api->getIndisCC($number);
            $answered = true;
            break;
        case 'c1_direccion_municipio':
            $direccion = $reqBody['result']['resolvedQuery'];
            var_dump($direccion);
            var_dump($municipio);
            $response = $api->getIndisAddress($direccion, $municipio);
            $answered = true;
            break;
        case 'c1_nit':
            $response = $api->getIndisNIT($number);
            $answered = true;
            break;
        case 'c1_niu':
            if(!$answered){
                $response = $api->getIndisNiu($number);    
            }
            break;
        case 'c1_nombre_municipio':
            $nombre = $reqBody['result']['resolvedQuery'];
            $response = $api->getIndisNombre($nombre, $municipio);
            $answered = true;
            break;
        case 'c2_cc':
            $response = $api->getSPCC($number);
            $answered = true;
            break;
        case 'c2_direccion_municipio':
            $direccion = $reqBody['result']['resolvedQuery'];
            $response = $api->getSPAddress($direccion, $municipio);
            $answered = true;
            break;
        case 'c2_nit':
            $response = $api->getSPNIT($number);
            $answered = true;
            break;
        case 'c2_niu':
            if(!$answered){
                $response = $api->getSPNiu($number);    
            }
            break;
        case 'c2_nombre_municipio':
            $nombre = $reqBody['result']['resolvedQuery'];
            $response = $api->getSPNombre($nombre, $municipio);
            $answered = true;
            break;
        default:
            break;
    }
}



header("Content-Type: application/json");
echo json_encode($response);
