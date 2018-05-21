<?php
include_once './html2json/HTMLTable2JSON.php';
require './download_attachments.php';
require './lib.php';

//$server = "http://localhost/chatbotCHEC/";
$server= "https://chatbotindisp.herokuapp.com/";
$helper = new HTMLTable2JSON();
$api = new chatBotApi();

get_attachments();
echo "attachments downloaded";
getDataFromFiles($server, $helper, $api, true);
getDataFromFiles($server, $helper, $api, false);

function getDataFromFiles($server, $helper, $api, $initial)
{
    $dir = new DirectoryIterator('./attachment/');
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
            echo "loading...\n";

            //Obtener el numero de la orden
            $file = $fileinfo->getFilename();
            if (substr($file, 0, 2) == "c-") {
                $orden_op = substr($file, 2);
                $orden_op = substr($orden_op, 0, -5);
            } else {
                $orden_op = substr($file, 0, -5);
            }
            //Obtener el estado de la orden
            $stringFile = file_get_contents('./attachment/' . $file);
            if (strpos($stringFile, 'CANCELADA') !== false) {
                $estado = "CANCELADO";
                if ($initial) {
                    continue;
                }
            } else {
                $estado = "ABIERTO";
            }

            //Obtener los datos de la suspension
            $data2Store = array();
            $helper->tableToJSON($server . 'attachment/' . $file, false);
            $jsonString = file_get_contents('./output.json');
            $arrayData = json_decode(utf8_encode($jsonString));

            //si el estado de la orden es ABIERTO se almacena normalmente el registro en base de datos
            if ($estado == "ABIERTO") {
                //Condicional si la columna Fecha es solo una
                if (isset($arrayData->Fecha)) {
                    for ($i = 0; $i < sizeof($arrayData->Transformador); $i++) {
                        $data2Store['COD_TRAFO'] = $arrayData->Transformador[$i]->cell_text;
                        $data2Store['NIU'] = $arrayData->Cuenta[$i]->cell_text;
                        $data2Store['FECHA_INICIO'] = $arrayData->{'Fecha'}[$i]->cell_text;
                        $data2Store['FECHA_FIN'] = $arrayData->{'Fecha'}[$i]->cell_text;
                        $data2Store['HORA_INICIO'] = $arrayData->{'Hora Inicio'}[$i]->cell_text;
                        $data2Store['HORA_FIN'] = $arrayData->{'Hora Fin'}[$i]->cell_text;
                        $data2Store['ESTADO'] = $estado;
                        $data2Store['ORDEN_OP'] = $orden_op;
                        $api->setSuspProgramada($data2Store);
                    }
                } else {
                    for ($i = 0; $i < sizeof($arrayData->Transformador); $i++) {
                        $data2Store['COD_TRAFO'] = $arrayData->Transformador[$i]->cell_text;
                        $data2Store['NIU'] = $arrayData->Cuenta[$i]->cell_text;
                        $data2Store['FECHA_INICIO'] = $arrayData->{'Fecha Inicio'}[$i]->cell_text;
                        $data2Store['FECHA_FIN'] = $arrayData->{'Fecha Fin'}[$i]->cell_text;
                        $data2Store['HORA_INICIO'] = $arrayData->{'Hora Inicio'}[$i]->cell_text;
                        $data2Store['HORA_FIN'] = $arrayData->{'Hora Fin'}[$i]->cell_text;
                        $data2Store['ESTADO'] = $estado;
                        $data2Store['ORDEN_OP'] = $orden_op;
                        $api->setSuspProgramada($data2Store);
                    }
                }
            } else {
                $api->updateSuspProgramada($orden_op);
            }
            unlink('./attachment/' . $file);
        }
    }
}
