<?php

include './download_attachments.php';

if (get_mail_body()) {
    echo "Carga Finalizada";

}else{
    echo "ocurrio un error en la carga";
}



