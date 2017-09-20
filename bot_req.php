<?php

require('./lib.php');

//Instancia de la API
$api = new chatBotApi();
$conversation_token="";
$NIU="";
$Nombre="";
$Telefono="";
$Direccion="";
$Cedula="";

//Obtener el cuerpo de la petición que viene de API.ai
$reqBody= $api->detectRequestBody();

//Asignación de parámetros
if (isset($reqBody['conversation_token'])) {
	$conversation_token = $reqBody['conversation_token'];
}
if (isset($reqBody['entities']['niu_number'][0]['value'])) {
	$NIU = $reqBody['entities']['niu_number'][0]['value'];
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


if($NIU == ""){
	if($Nombre==""){
		if($Telefono==""){
			if($Direccion==""){
				if($Cedula==""){
					$response = "Lo siento, no pude encontrar la respuesta a tu petición";
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
}








echo json_encode($response);
?>