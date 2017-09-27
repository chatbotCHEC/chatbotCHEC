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
if (isset($reqBody['result']['parameters']['fecha'])) {
	$fecha = $reqBody['result']['parameters']['fecha'];
}
if (isset($reqBody['result']['parameters']['time'])) {
	$time = $reqBody['result']['parameters']['time'];
}
if (isset($reqBody['result']['parameters']['condition'])) {
	$condition = $reqBody['result']['parameters']['condition'];
}
if (isset($reqBody['result']['parameters']['cod_circuit'])) {
	$cod_circuit = $reqBody['result']['parameters']['cod_circuit'];
}
if (isset($reqBody['result']['parameters']['suscribers'])) {
	$suscribers = $reqBody['result']['parameters']['suscribers'];
}

if($fecha!="" && $time!="" && $condition!="" && $cod_circuit!="" && $suscribers!=""){
    $data = ['FECHA'=> $fecha, 'HORA'=> $time, 'ESTADO'=>$condition, 'CIRCUITO'=>$cod_circuit, 'N_SUSCRIPTORES'=>$suscribers];
	$api->setIndispCircuito($data);
}elseif ($fecha!="" && $time!="" && $condition!="" && $cod_circuit!="") {
    $data = ['FECHA'=> $fecha, 'HORA'=> $time, 'ESTADO'=>$condition, 'CIRCUITO'=>$cod_circuit, 'N_SUSCRIPTORES'=>'NULL'];
    $api->setIndispCircuito($data);
}