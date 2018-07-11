<?php
set_time_limit(3000);

require './lib.php';
require 'download_attachments.php';

$api = new chatBotApi();
//Para Descargar
get_attachments_efectivas();

$dir = new DirectoryIterator('./attachment_efectivas/');
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        //echo "loading...\n";

        //Obtener el numero de la orden
        $file = $fileinfo->getFilename();
        $datos = cargaSuspensionesEfectivas($file);

        foreach ($datos as $fila) {

            $resultado = $api->setSuspensionEfectiva($fila);
        }

        unlink('./attachment_efectivas/' . $file);

    }

}

//$datos = cargaSuspensionesEfectivas("OTs_suspension_efectivas_2018_03_06_10_20_am.xls");

//$resultado = $api->setSuspensionEfectiva($datos);

function cargaSuspensionesEfectivas($file)
{

    require_once "./PHPExcel-1.8/Classes/PHPExcel.php";
    require_once './IOFactory.php';

    //Variable con el nombre del archivo
    $nombreArchivo = './attachment_efectivas/' . $file;
    // Cargo la hoja de cálculo
    $objPHPExcel = PHPExcel_IOFactory::load($nombreArchivo);

    //Asigno la hoja de calculo activa
    $objPHPExcel->setActiveSheetIndex(0);
    //Obtengo el numero de filas del archivo
    $numRows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();

    $efec = array();

    for ($i = 3; $i <= $numRows; $i++) {

        $id_orden = strval($objPHPExcel->getActiveSheet()->getCell('B' . $i)->getCalculatedValue());
        $niu = strval($objPHPExcel->getActiveSheet()->getCell('C' . $i)->getCalculatedValue());
        $fecha_atencion = $objPHPExcel->getActiveSheet()->getCell('F' . $i)->getCalculatedValue();
        $hora_ini = $objPHPExcel->getActiveSheet()->getCell('H' . $i)->getCalculatedValue();
        $hora_fin = $objPHPExcel->getActiveSheet()->getCell('I' . $i)->getCalculatedValue();
        $descripcion = $objPHPExcel->getActiveSheet()->getCell('Q' . $i)->getCalculatedValue();
        $valor = $objPHPExcel->getActiveSheet()->getCell('R' . $i)->getCalculatedValue();

        if (strpos($valor, "_")) {
            $posaux = strpos($valor, "_") + 2;
            if (substr($valor, $posaux, 1) == "-") {
                $posicion = strpos($valor, "_") + 1;
                $tipo_registro = substr($valor, $posicion, 1);

                if ($tipo_registro == '3') {
                    $valor = 's';
                    $fila = array($id_orden, $niu, $fecha_atencion, $hora_ini, $hora_fin, $descripcion, $valor);
                    array_push($efec, $fila);
                } elseif ($tipo_registro == '8') {
                    $valor = 'r';
                    $fila = array($id_orden, $niu, $fecha_atencion, $hora_ini, $hora_fin, $descripcion, $valor);
                    array_push($efec, $fila);
                }
            }

        }

    }

    return $efec;

}
