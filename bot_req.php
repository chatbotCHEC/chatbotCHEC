<?php

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

//Obtener los contextos de la petición 
foreach ($reqBody['result']['contexts'] as $valor) {
    array_push($contexts, $valor['name']);
}

//Verifica si de la petición se recibe la entidad number
//PENDIENTE: verificar cuando lleguen entidades Nombre, direccion
if (isset($reqBody['result']['parameters']['number'])) {
    $number = strval($reqBody['result']['parameters']['number']);
}
if (isset($reqBody['result']['parameters']['given-name'])) {
    $Nombre = $reqBody['result']['parameters']['given-name']." ".$reqBody['result']['parameters']['last-name'];
}

//Switch que determina cuál es el contexto principal de la petición y ejecuta una función del objeto api correspondientemente.
switch ($contexts[0]) {
    case 'c1_cc':
        $response = $api->getNiuFromCedula($number, $contexts[1]);
        break;
    case 'c1_direccion':
        break;    
    case 'c1_nit':
        $response = $api->getNiuFromNIT($number, $contexts[1]);
        break;
    case 'c1_niu':
        $response = $api->getIndisNiu($number);
        break;
    case 'c1_nombre':
        break;
    case 'c2_cc':
        $response = $api->getNiuFromCedula($number, $contexts[1]);
        break;
    case 'c2_direccion':
        break;    
    case 'c2_nit':
        $response = $api->getNiuFromNIT($number, $contexts[1]);
        break;
    case 'ic2_nu':
        $response = $api->getSPNiu($number);
        break;
    case 'c2_nombre':
        break;
}


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
header("Content-Type: application/json");
echo json_encode($response);
