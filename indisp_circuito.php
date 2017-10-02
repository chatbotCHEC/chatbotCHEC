<?php

require('./lib.php');

//Instancia de la API
$api = new chatBotApi();
//Obtener el cuerpo de la petición que viene de API.ai
$reqBody= $api->detectRequestBody();

$fecha = "";
$time = "";
$condition = "";
$cod_circuit = "";
$suscribers = "";

//Asignación de parámetros
if (isset($reqBody['result']['parameters']['global'])) {
	$global = $reqBody['result']['parameters']['global'];
}


$data = $api->getIndisponibilidadCircuitoData($global);

$response = $api->setIndispCircuito($data);

echo "success";

