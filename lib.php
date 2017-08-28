<?php

//Credenciales BD
$host = "167.114.131.74";
$user = "CBQT";
$pass = "admin1234";
$db = "chec";
//$port ="3306";


//Obtener el cuerpo de la petición POST del chatbot
function detectRequestBody() {
    $rawInput = fopen('php://input', 'r');
    $tempStream = fopen('php://temp', 'r+');
    stream_copy_to_stream($rawInput, $tempStream);
    rewind($tempStream);

    return $tempStream;
}

?>