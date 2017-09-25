<?php
require('./consultas.php');
class chatBotAPI {
    //Credenciales BD
    private $host = "gestion-educativa.database.windows.net";
    private $user = "usr_gestion_educativa";
    private $pass = "YXj0q9JctrQoatODR4lr";
    private $db = "GestionComercialCHEC";
    
    //conexion a BD
    private $con;

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
        try{
            $this->con = new PDO('sqlsrv:Server='.$this->host.'; Database='.$this->db, $this->user.'@gestion-educativa',$this->pass);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            echo "Error de coneccion". $e->getMessage();
        }  
    }

    //Obtener los datos del usuario a partir del NIU
    public function getUserData($NIU){
        $db_response=getData($this->con, $NIU);
        //Verificar si se encontró el NIU
        if(!(isset($db_response['NIU'])) || $db_response['NIU']==""){
            //Respuesta para cuando no se encuentra el NIU
            $json['speech']="No se ha encontrado un usuario con el número de cuenta solicitado";
            $json['displayText']="No se ha encontrado un usuario con el número de cuenta solicitado";
        }else {
            //Verificar si el NIU consultado tiene telefono registrado
            if($db_response['TELEFONO']!=""){
                //Respuesta para cuando sí hay un teléfono registrado
                $json['speech']="El nombre del usuario con el número de cuenta ".$db_response['NIU'].", es ".$db_response['NOMBRE'].". Su predio se encuentra en la dirección ".$db_response['DES_DIRECCION']." y su número de teléfono registrado es ".$db_response['TELEFONO'].".";
                $json['displayText']="El nombre del usuario con el número de cuenta ".$db_response['NIU'].", es ".$db_response['NOMBRE'].". Su predio se encuentra en la dirección ".$db_response['DES_DIRECCION']." y su número de teléfono registrado es ".$db_response['TELEFONO'].".";
            }else{
                //Respuesta para cuando no hay un teléfono registrado
                $json['speech']="El nombre del usuario con el número de cuenta ".$db_response['NIU'].", es ".$db_response['NOMBRE'].". Su predio se encuentra en la dirección ".$db_response['DES_DIRECCION']." y no tenemos registrado ningún número telefónico.";	
                $json['displayText']="El nombre del usuario con el número de cuenta ".$db_response['NIU'].", es ".$db_response['NOMBRE'].". Su predio se encuentra en la dirección ".$db_response['DES_DIRECCION']." y no tenemos registrado ningún número telefónico.";	
            }
            
            //Consultar si el NIU está asociado a alguna indisponibilidad sin resolver
            $disp_response = getDisponibilidad($this->con, $db_response['COD_TRAFO']);
            if(!(is_null($disp_response))){
                if($disp_response['ESTADO']=='APERTURA'){
                    //Respuesta para cuando hay una indisponibilidad
                    $json['speech']=$json['speech']."\n Para este usuario, se ha presentado un corte del servicio en la fecha: ".$disp_response['FECHA']." a las ".$disp_response['HORA'].". Estamos trabajando para reestablecer su servicio lo más pronto posible.";
                    $json['displayText']=$json['speech']."\n Para este usuario, se ha presentado un corte del servicio en la fecha: ".$disp_response['FECHA']." a las ".$disp_response['HORA'].". Estamos trabajando para reestablecer su servicio lo más pronto posible.";
                }else{
                    //Respuesta para cuando no hay indisponibilidad
                    $json['speech']=$json['speech']."\n Para este usuario, no se presentan inconvenientes registrados.";
                    $json['displayText']=$json['speech']."\n Para este usuario, no se presentan inconvenientes registrados.";
                }
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
}







?>