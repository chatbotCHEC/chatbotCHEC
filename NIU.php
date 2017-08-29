<?php

require('./lib.php');

//Instancia de la API
$api = new chatBotApi();
$NIU="";
$Nombre="";
$Telefono="";

//Obtener el cuerpo de la petición que viene de API.ai
$reqBody= $api->detectRequestBody();

//Asignación de parámetros
if (isset($reqBody['result']['parameters']['NIU'])) {
	$NIU = $reqBody['result']['parameters']['NIU'];
}
if (isset($reqBody['result']['parameters']['Nombre'])) {
	$Nombre = $reqBody['result']['parameters']['Nombre'];
}
if (isset($reqBody['result']['parameters']['Telefono'])) {
	$Telefono = $reqBody['result']['parameters']['Telefono'];
}

if($NIU == ""){
	if($Nombre==""){
		if($Telefono==""){
			$response = "Lo siento, no pude encontrar la respuesta a tu petición";
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