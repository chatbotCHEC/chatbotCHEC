<?php

require('./lib.php');
require('./consultas.php');

$NIU = $_REQUEST['NIU'];
try{
	$con = new PDO('mysql:host=167.114.131.74; dbnme=chec',$user,$pass);
	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
}catch(PDOException $e){
	echo "Error de coneccion". $e->getMessage();
}
$db_response=getData($con, $NIU);


//Verificar si el NIU consultado tiene telefono registrado
if($db_response['Data']['TELEFONO']!=""){
	$json['speech']="El nombre del usuario con el número de cuenta ".$db_response['Data']['NIU'].", es ".$db_response['Data']['NOMBRE'].". Su predio se encuentra en la dirección ".$db_response['Data']['DES_DIRECCION']." y su número de teléfono registrado es ".$db_response['Data']['TELEFONO'].".";
	$json['displayText']="El nombre del usuario con el número de cuenta ".$db_response['Data']['NIU'].", es ".$db_response['Data']['NOMBRE'].". Su predio se encuentra en la dirección ".$db_response['Data']['DES_DIRECCION']." y su número de teléfono registrado es ".$db_response['Data']['TELEFONO'].".";
}else{
	$json['speech']="El nombre del usuario con el número de cuenta ".$db_response['Data']['NIU'].", es ".$db_response['Data']['NOMBRE'].". Su predio se encuentra en la dirección ".$db_response['Data']['DES_DIRECCION']." y no tenemos registrado ningún número telefónico.";	
	$json['displayText']="El nombre del usuario con el número de cuenta ".$db_response['Data']['NIU'].", es ".$db_response['Data']['NOMBRE'].". Su predio se encuentra en la dirección ".$db_response['Data']['DES_DIRECCION']." y no tenemos registrado ningún número telefónico.";	
}

$disp_response = getDisponibilidad($con, $db_response['Data']['COD_TRAFO']);
if($disp_response['ESTADO']=='APERTURA'){
	$json['speech']=$json['speech']."\n Para este usuario, se ha presentado un corte del servicio en la fecha: ".$disp_response['FECHA']." a las ".$disp_response['HORA'].". Estamos trabajando para reestablecer su servicio lo más pronto posible.";
	$json['displayText']=$json['speech']."\n Para este usuario, se ha presentado un corte del servicio en la fecha: ".$disp_response['FECHA']." a las ".$disp_response['HORA'].". Estamos trabajando para reestablecer su servicio lo más pronto posible.";
}else{
	$json['speech']=$json['speech']."\n Para este usuario, no se presentan inconvenientes registrados.";
	$json['displayText']=$json['speech']."\n Para este usuario, no se presentan inconvenientes registrados.";
}


echo json_encode($json);
?>