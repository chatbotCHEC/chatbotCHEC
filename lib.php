<?php
require('./consultas.php');
class chatBotAPI {
    //Credenciales BD
    private $host = "ds147884.mlab.com:47884";
    private $user = "heroku_8hbdg59z";
    private $pass = "e0eutnmkoq1f4n339dkueaggbb";
    private $db = "heroku_8hbdg59z";
    
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
            $this->con = new MongoDB\Driver\Manager("mongodb://".$this->host);
        } catch (MongoDB\Driver\Exception\Exception $e) {
            $filename = basename(__FILE__);
            echo "The $filename script has experienced an error.\n"; 
            echo "It failed with the following exception:\n";
            echo "Exception:", $e->getMessage(), "\n";
            echo "In file:", $e->getFile(), "\n";
            echo "On line:", $e->getLine(), "\n";
        }
        
    }

    public function respuesta($persona){
        //Verificar si se encontró el NIU
        if(!(isset($persona->NIU)) || $persona->NIU==""){
            //Respuesta para cuando no se encuentra el NIU
            $json['speech']="No se ha encontrado un usuario con el dato indicado";
            $json['displayText']="No se ha encontrado un usuario con el dato indicado";
        }else {
            //Verificar si el NIU consultado tiene telefono registrado
            if($persona->TELEFONO!="" && $persona->TELEFONO!="NULL" ){
                //Respuesta para cuando sí hay un teléfono registrado
                $json['speech']="El nombre del usuario con el número de cuenta ".$persona->NIU.", es ".$persona->NOMBRE.". Su predio se encuentra en la dirección ".$persona->DIRECCION." y su número de teléfono registrado es ".$persona->TELEFONO.".";
                $json['displayText']="El nombre del usuario con el número de cuenta ".$persona->NIU.", es ".$persona->NOMBRE.". Su predio se encuentra en la dirección ".$persona->DIRECCION." y su número de teléfono registrado es ".$persona->TELEFONO.".";               
            }else{
                //Respuesta para cuando no hay un teléfono registrado
                $json['speech']="El nombre del usuario con el número de cuenta ".$persona->NIU.", es ".$persona->NOMBRE.". Su predio se encuentra en la dirección ".$persona->DIRECCION." y no tenemos registrado ningún número telefónico.";	
                $json['displayText']="El nombre del usuario con el número de cuenta ".$persona->NIU.", es ".$persona->NOMBRE.". Su predio se encuentra en la dirección ".$persona->DIRECCION." y no tenemos registrado ningún número telefónico.";	
            }  
        } 
        return $json;
    }

    public function respuesta_plural($personas){
        //Verificar si se encontró alguna cuenta con el nombre asociado
        if(is_null($personas)||count($personas)==0){
            //Respuesta para cuando no se encuentra la cuenta con el nombre asociado
            $json['speech']="No se ha encontrado ninguna cuenta con el dato ingresado";
            $json['displayText']="No se ha encontrado ninguna cuenta con el dato ingresado";
        }else{
            $json['speech']="Hemos encontrado las siguientes cuentas asociadas con el dato dado";
            $json['displayText']="Hemos encontrado las siguientes cuentas asociadas con el dato dado\n";
            foreach ($personas as $persona) {
                $json['speech']=$json['speech']."\n - Nombre: ".$persona->NOMBRE."\n - Dirección: ".$persona->DIRECCION."\n - Numero de cuenta: ".$persona->NIU;
                $json['displayText']=$json['displayText']."---------------\n - Nombre: ".$persona->NOMBRE."\n - Dirección: ".$persona->DIRECCION."\n - Numero de cuenta: ".$persona->NIU;
            }
            $json['speech']=$json['speech']."\n A continuación, digita el número de cuenta correspondiente a tu solicitud de la siguiente manera: \n NIU: #####";
            $json['displayText']=$json['displayText']."\n A continuación, digita el número de cuenta correspondiente a tu solicitud de la siguiente manera: \n NIU: #####";
        }
        return $json;
    }
    
    //Obtener los datos del usuario a partir del NIU
    public function getUserData($NIU){
        $persona=getData($NIU, $this->con);
        return $this->respuesta($persona);
    }

    public function getNiuFromCedula($cedula){
        $persona = getNIUwithCedula($this->con, $cedula);
        return $this->respuesta_plural($persona);
    }
    public function getNiuFromNIT($nit){
        $persona = getNIUwithNIT($this->con, $nit);
        return $this->respuesta_plural($persona);
    }

    public function getNiuFromName($nombre){
        $palabras = explode(" ", strtoupper($nombre));
        $personas = getNIUwithName($this->con, $palabras);
        return $this->respuesta_plural($personas);
    }

    public function getNiuFromTelephone($telefono){
        $persona = getNIUwithTel($this->con, $telefono);
        return $this->respuesta_plural($persona);
    }


    
    //PENDIENTE DE MEJORA, AUN NO SE DEBE IMPLEMENTAR
    public function getNiuFromAddress($direccion){
        $direccionesProcesadas = $this->processAddress($direccion);
        $personas = getNIUwithAddress($this->con, $direccionesProcesadas);
        return $this->respuesta_plural($personas);
    }



    public function processAddress($direccion){
        $direcNoSymbols = $direccion;
        $direcNoHyphens = $direccion;
        if(strpos($direccion, '#')){
            $direcNoSymbols = substr_replace($direccion, ' ', strpos($direccion, '#'), 1);
        }
        if(strpos($direccion, '-')){
            $direcNoHyphens = substr_replace($direcNoSymbols, ' ', strpos($direcNoSymbols, '-'), 1);
        }
        $output = preg_replace('!\s+!', ' ', $direcNoHyphens);
        $array = explode(" ", strtoupper($output));
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

    public function parseNumbers(){
        $filter = [];
        $query = new MongoDB\Driver\Query($filter);
        $result = $this->con->executeQuery("chatbot_db.usuarios", $query);
        foreach ($result as $r) {
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update(
                ['_id' => $r->_id],
                ['$set' => ['NIU' => strval($r->NIU), 'DOCUMENTO'=> strval($r->DOCUMENTO), 'TELEFONO'=> strval($r->TELEFONO)]],
                ['multi' => false, 'upsert' => false]
            );
            $result = $this->con->executeBulkWrite('chatbot_db.usuarios', $bulk);
        }
        echo "\n converted ahora si";
    }

}







?>