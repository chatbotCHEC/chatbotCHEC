<?php
require('./consultas.php');
class chatBotAPI {
    //Credenciales BD
    private $host = "localhost:27017";
    private $user = "";
    private $pass = "";
    private $db = "chatbot_db";
    private $request_token = "b18da64ff23ffba80dd4db93cfdeb8e2";
    
    //conexion a BD
    private $con;
    private $bd;

    public function __construct(){
        $this->connectToDB();
    }

    //Obtener el cuerpo de la petición POST del chatbot
    public function detectRequestBody() {
        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, TRUE);
        return $input;
    }

    //Conectar a la Base de datos
    public function connectToDB(){
        try {
            $this->con = new MongoDB\Driver\Manager("mongodb://localhost:27017");
        } catch (MongoDB\Driver\Exception\Exception $e) {
            $filename = basename(__FILE__);
            echo "The $filename script has experienced an error.\n"; 
            echo "It failed with the following exception:\n";
            echo "Exception:", $e->getMessage(), "\n";
            echo "In file:", $e->getFile(), "\n";
            echo "On line:", $e->getLine(), "\n";
        }
        
    }

    //Obtener los datos del usuario a partir del NIU
    public function getUserData($NIU){
        $db_response=getData($NIU, $this->con);
        

        //Verificar si se encontró el NIU
        if(!(isset($db_response->NIU)) || $db_response->NIU==""){
            //Respuesta para cuando no se encuentra el NIU
            $json['messages'][0]['content']="No se ha encontrado un usuario con el número de cuenta solicitado";
        }else {
            //Verificar si el NIU consultado tiene telefono registrado
            if($db_response->TELEFONO!="" && $db_response->TELEFONO!="NULL" ){
                //Respuesta para cuando sí hay un teléfono registrado
                $json['messages'][0]['content']="El nombre del usuario con el número de cuenta ".$db_response->NIU.", es ".$db_response->NOMBRE.". Su predio se encuentra en la dirección ".$db_response->DIRECCION." y su número de teléfono registrado es ".$db_response->TELEFONO.".";
            }else{
                //Respuesta para cuando no hay un teléfono registrado
                $json['messages'][0]['content']="El nombre del usuario con el número de cuenta ".$db_response->NIU.", es ".$db_response->NOMBRE.". Su predio se encuentra en la dirección ".$db_response->DIRECCION." y no tenemos registrado ningún número telefónico.";	
            }
                  
        }  
        return $json;
    }

    public function getNiuFromName($nombre){
        $palabras = explode(" ", strtoupper($nombre));
        $niu = getNIUwithName($this->con, $palabras);
        if(!(isset($niu['NIU'])) || is_null($niu['NIU'])){
            $json['speech']="No he podido encontrar ninguna cuenta asociada con el nombre ingresado";
            $json['displayText']="No he podido encontrar ninguna cuenta asociada con el nombre ingresado";
            return $json;
        }else{
            return $this->getUserData($niu['NIU']);
        }
    }

    public function getNiuFromTelephone($telefono){
        $niu = getNIUwithTel($this->con, $telefono);
        
        if(!(isset($niu['NIU'])) || is_null($niu['NIU'])){
            $json['speech']="No he podido encontrar ninguna cuenta asociada con el teléfono ingresado";
            $json['displayText']="No he podido encontrar ninguna cuenta asociada con el nombre ingresado";
            return $json;
        }else{
            return $this->getUserData($niu['NIU']);
        }
    }

    public function getNiuFromCedula($cedula){
        $niu = getNIUwithCedula($this->con, $cedula);
        
        if(!(isset($niu['NIU'])) || is_null($niu['NIU'])){
            $json['speech']="No he podido encontrar ninguna cuenta asociada con la cédula ingresada";
            $json['displayText']="No he podido encontrar ninguna cuenta asociada con la cédula ingresada";
            return $json;
        }else{
            return $this->getUserData($niu['NIU']);
        }
    }

    

    public function getNiuFromAddress($direccion){
        $direcNoSymbols = $direccion;
        $direcNoHyphens = $direccion;
        if(strpos($direccion, '#')){
            $direcNoSymbols = substr_replace($direccion, ' ', strpos($direccion, '#'), 1);
        }
        if(strpos($direccion, '-')){
            $direcNoHyphens = substr_replace($direcNoSymbols, ' ', strpos($direcNoSymbols, '-'), 1);
        }
    
        $output = preg_replace('!\s+!', ' ', $direcNoHyphens);
        $direccionDiv = explode(" ", strtoupper($output));
        $direccionesProcesadas = $this->processAddress($direccionDiv);
    

        
        $niu = getNIUwithAddress($this->con, $direccionesProcesadas);
        
        if(!(isset($niu['NIU'])) || is_null($niu['NIU'])){
            $json['speech']="No he podido encontrar ninguna cuenta asociada con la dirección ingresada";
            $json['displayText']="No he podido encontrar ninguna cuenta asociada con la dirección ingresada";
            return $json;
        }else{
            return $this->getUserData($niu['NIU']);
        }
    }



    public function processAddress($array){
        foreach ($array as $i => $value) {
            if($value == "CARRERA" || $value == "CRA" || $value == "CAR" || $value == "CR"){
                $array[$i]="CRA";
            }
            if($value == "CALLE" || $value == "CLL" || $value == "CALL" || $value == "CALL"){
                $array[$i]="CLL";
            }
            if($value == "AVENIDA" || $value == "AV" || $value == "AVE" || $value == "AVDA" ){
                $array[$i]="AVE";
                array_push($array, "AV");
                array_push($array, "AVDA");
            }
            if($value == "APARTAMENTO" || $value == "APTO" || $value == "AP"){
                $array[$i]="APT";
                array_push($array, "APTO");
            }
            if($value == "BLOQUE" || $value == "BLQ" || $value == "BL"){
                $array[$i]="BLQ";
                array_push($array, "BLO");
            }
            if($value == "LOCAL" || $value == "LOC"){
                $array[$i]="LOC";
            }
            if($value == "VEREDA" || $value == "VDA"){
                $array[$i]="VDA";
            }
            if($value == "SECTOR" || $value == "SEC" || $value == "SECT"){
                $array[$i]="SECTOR";
                array_push($array, "SEC");
                array_push($array, "SECT");
            }
        }
        return $array;
    }

    public function htttpRequest($data, $url){
        $opts = array(
            'http'=>array(
              'method'=>"POST",
              'header'=>"Authorization: Token ".$this->request_token."\r\n",
              'content'=>http_build_query($data)
            )
          );
          
            $context = stream_context_create($opts);
          
            $result = file_get_contents($url, false, $context);
            if ($result === FALSE) { 
                return "error";
             }
            return $result;
    }
}







?>