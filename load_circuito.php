<?php
error_reporting(-1);
ini_set('display_errors', 'On');
include './download_attachments.php';

if (get_mail_body()) {
    //echo "Carga Finalizada";

}else{
    //echo "ocurrio un error en la carga";
}



