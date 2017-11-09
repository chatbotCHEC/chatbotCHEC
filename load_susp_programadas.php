<?php
include_once './html2json/HTMLTable2JSON.php';
require('./download_attachments.php');

$server= "http://localhost/chatbotchec/";
$helper = new HTMLTable2JSON();

$dir = new DirectoryIterator('./attachment/');
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {

        //Obtener el numero de la orden
        $orden_op = substr($fileinfo->getFilename(), 0, -4);

        $stringFile = file_get_contents('./attachment/'.$fileinfo->getFilename());

        if (strpos($stringFile, 'CANCELADA') !== false) {
            $estado="CANCELADO";
        }else {
            $estado="ABIERTO";
        }
    }
}

//$helper->tableToJSON($server.'attachment/'.$file);
