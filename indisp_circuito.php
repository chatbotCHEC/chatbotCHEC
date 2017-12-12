<?php


function saveIndispCircuito($global){
	require('./lib.php');
	//Instancia de la API
	$api = new chatBotApi();
	
	$fecha = "";
	$time = "";
	$condition = "";
	$cod_circuit = "";
	$suscribers = "";
	
	
	$data = $api->getIndisponibilidadCircuitoData($global);
	
	$response = $api->setIndispCircuito($data);
	
	echo "success";
}

