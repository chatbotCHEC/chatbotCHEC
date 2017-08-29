<?php

require('./lib.php');

//Instancia de la API
$api = new chatBotApi();
$NIU="";
$Nombre="";

//Obtener el cuerpo de la petición que viene de API.ai
$reqBody= $api->detectRequestBody();

//Asignación de parámetros
if (isset($reqBody['result']['parameters']['NIU'])) {
	$NIU = $reqBody['result']['parameters']['NIU'];
}
if (isset($reqBody['result']['parameters']['Nombre'])) {
	$Nombre = $reqBody['result']['parameters']['Nombre'];
}

if($NIU == ""){
	if($Nombre==""){
		$response = "Lo siento, no pude encontrar la respuesta a tu petición";
	}else{
		$response = $api->getNiuFromName($Nombre);
	}
}else{
	$response = $api->getUserData($NIU);
}








echo json_encode($response);
?>