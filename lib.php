<?php
require('./consultas.php');
class chatBotAPI {
    //Credenciales BD
    private $host = "167.114.131.74";
    private $user = "CBQT";
    private $pass = "admin1234";
    private $db = "chec";
    
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
            $this->con = new PDO('mysql:host='.$this->host.'; dbnme='.$this->db, $this->user,$this->pass);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            echo "Error de coneccion". $e->getMessage();
        }  
    }

    //Obtener los datos del usuario a partir del NIU
    public function getUserData($NIU){
        $db_response=getData($this->con, $NIU);
        
        //Verificar si se encontró el NIU
        if($db_response['NIU']==""){
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
        return $json;
    }

    public function getNiuFromName($nombre){
        $palabras = explode(" ", strtoupper($nombre));
        $niu = getNIUwithName($this->con, $palabras);
        if(is_null($niu['NIU'])){
            $json['speech']="No he podido encontrar ninguna cuenta asociada con el nombre ingresado";
        }else{
            return $this->getUserData($niu['NIU']);
        }
    }
}







?>