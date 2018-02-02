<?php

require './lib.php';

//Instancia de la API
$api = new chatBotApi();
$NIU = "";
$Nombre = "";
$Telefono = "";
$Direccion = "";
$Cedula = "";
$NIT = "";
$number = "";

$contexts = array();

//Obtener el cuerpo de la petición que viene de API.ai
$reqBody = $api->detectRequestBody();

foreach ($reqBody['result']['contexts'] as $valor) {

    array_push($contexts, $valor['name']);

}
;
if (isset($reqBody['result']['parameters']['number'])) {
    $number = $reqBody['result']['parameters']['number'];
    }


switch ($contexts[0]) {
    case 'c1_cc':
        getIndisCedula($number);
        break;
    case 'c1_direccion':
        # code...
        break;    
    case 'c1_nit':
        getIndisNit($number);
        # code...
        break;
    case 'c1_niu':
        getIndisNiu($number);
        # code...
        break;
    case 'c1_nombre':
        # code...
        break;
    case 'c2_cc':
        getSPCedula($number);
        # code...
        break;
    case 'c2_direccion':
        # code...
        break;    
    case 'c2_nit':
        getSPNit($number);
        # code...
        break;
    case 'c2_niu':
        getSPNiu($number);
        # code...
        break;
    case 'c2_nombre':
        # code...
        break;
}

$response = $contexts;

//Asignación de parámetros
/* if (isset($reqBody['result']['parameters']['number'])) {
$NIU = $reqBody['result']['parameters']['niu'];
}
if (isset($reqBody['result']['parameters']['Nombre'])) {
$Nombre = $reqBody['result']['parameters']['Nombre'];
}
if (isset($reqBody['result']['parameters']['Telefono'])) {
$Telefono = $reqBody['result']['parameters']['Telefono'];
}
if (isset($reqBody['result']['parameters']['Direccion'])) {
$Direccion = $reqBody['result']['parameters']['Direccion'];
}
if (isset($reqBody['result']['parameters']['Cedula'])) {
$Cedula = $reqBody['result']['parameters']['Cedula'];
}
if (isset($reqBody['result']['parameters']['NIT'])) {
$NIT = $reqBody['result']['parameters']['NIT'];
}

//este orden va con la misma jerarquia de pregunta en dialogflow
if($NIU == ""){
if($Nombre==""){
if($Telefono==""){
if($Direccion==""){
if($Cedula==""){
if($NIT==""){
$response['displayText'] = "Lo siento, no pude encontrar la respuesta a tu petición";
}else{
$response = $api->getNiuFromNIT($NIT);
}
}else{
$response = $api->getNiuFromCedula($Cedula);
}
}else {
$response = $api->getNiuFromAddress($Direccion);
}
}else{
$response = $api->getNiuFromTelephone($Telefono);
}
}else{
$response = $api->getNiuFromName($Nombre);
}
}else{
$response = $api->getUserData($NIU);
} */
echo json_encode($response);c
