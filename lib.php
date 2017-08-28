<?php

//Credenciales BD
$host = "167.114.131.74";
$user = "CBQT";
$pass = "admin1234";
$db = "chec";
//$port ="3306";


//Obtener el cuerpo de la petición POST del chatbot
function detectRequestBody() {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);
    return $input;
}

?>