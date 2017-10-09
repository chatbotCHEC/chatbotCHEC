<?php

require('./lib.php');

//Instancia de la API
$api = new chatBotApi();
$NIU="";
$Nombre="";
$Telefono="";
$Direccion="";
$Cedula="";
$NIT="";

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
if (isset($reqBody['result']['parameters']['Direccion'])) {
	$Direccion = $reqBody['result']['parameters']['Direccion'];
}
if (isset($reqBody['result']['parameters']['Cedula'])) {
	$Cedula = $reqBody['result']['parameters']['Cedula'];
}
if (isset($reqBody['result']['parameters']['NIT'])) {
	$NIT = $reqBody['result']['parameters']['NIT'];
}


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
}
var_dump($NIU);
echo json_encode($response);
?>