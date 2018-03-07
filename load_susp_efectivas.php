<?php
set_time_limit(3000); 
require './lib.php';

$api = new chatBotApi();

$datos = cargaSuspensionesEfectivas("OTs_suspension_efectivas_2018_02_28_07_32_am.xls");


foreach ($datos as $fila){
    
    $resultado = $api->setSuspensionEfectiva($fila);
}

var_dump($resultado);

function cargaSuspensionesEfectivas($file){
    
    require_once "./PHPExcel-1.8/Classes/PHPExcel.php";
    require_once './IOFactory.php';

	//Variable con el nombre del archivo
	$nombreArchivo = './attachment_efectivas/'.$file;
	// Cargo la hoja de cálculo
	$objPHPExcel = PHPExcel_IOFactory::load($nombreArchivo);
	
	//Asigno la hoja de calculo activa
	$objPHPExcel->setActiveSheetIndex(0);
	//Obtengo el numero de filas del archivo
	$numRows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();

    $efec = array();

    for($i = 3; $i <= $numRows; $i++){
    
        $id_orden = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
        $niu = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
        $fecha_atencion = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
        $hora_ini = $objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue();
        $hora_fin = $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue();
        $descripcion = $objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getCalculatedValue();
        $valor = $objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue();

        $fila = array($id_orden, $niu, $fecha_atencion, $hora_ini, $hora_fin, $descripcion, $valor);
    
        array_push($efec, $fila);

    }




    return $efec;

}

?>