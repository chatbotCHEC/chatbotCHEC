<?php
require './consultas.php';
class chatBotAPI
{

    //Credenciales HEROKU Mlab
    private $host = "mongodb://heroku_69tb2th4:m2oheamen7422pmnq3htdb56dt@ds113775.mlab.com:13775/heroku_69tb2th4";

    //Credenciales Localhost
    //private $host = "mongodb://localhost:27017/chatbot_db";

    //conexion a BD
    private $con;
    private $bd;

    public function __construct()
    {
        $this->connectToDB();
    }

    //Obtener el cuerpo de la petición POST del chatbot
    public function detectRequestBody()
    {
        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);
        return $input;
    }

    //Conectar a la Base de datos
    public function connectToDB()
    {
        try {
            $this->con = new MongoDB\Driver\Manager($this->host);
        } catch (MongoDB\Driver\Exception\Exception $e) {
            $filename = basename(__FILE__);
            echo "The $filename script has experienced an error.\n";
            echo "It failed with the following exception:\n";
            echo "Exception:", $e->getMessage(), "\n";
            echo "In file:", $e->getFile(), "\n";
            echo "On line:", $e->getLine(), "\n";
        }

    }

    public function respuesta($persona)
    {
        //Verificar si se encontró el NIU
        if (!(isset($persona->NIU)) || $persona->NIU == "") {
            //Respuesta para cuando no se encuentra el NIU
            $json['speech'] = "No se ha encontrado un usuario con el dato indicado";
            $json['displayText'] = "No se ha encontrado un usuario con el dato indicado";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => "No se ha encontrado ninguna cuenta con el dato ingresado. \n ¿Deseas consultar algo más?",
                            'reply_markup' => array(
                                'keyboard' => array(
                                    array(
                                        array(
                                            'text' => 'Sí ✔️',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => 'No ❌',
                                            'callback_data' => 'No',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            );

        } else {
            //Verificar si el NIU consultado tiene telefono registrado
            if ($persona->TELEFONO != "" && $persona->TELEFONO != "NULL") {
                //Respuesta para cuando sí hay un teléfono registrado
                $json['speech'] = "El nombre del usuario con el número de cuenta " . $persona->NIU . ", es " . $persona->NOMBRE . ". Su predio se encuentra en la dirección " . $persona->DIRECCION . " - " . $persona->MUNICIPIO . " y su número de teléfono registrado es " . $persona->TELEFONO . ".";
                $json['displayText'] = "El nombre del usuario con el número de cuenta " . $persona->NIU . ", es " . $persona->NOMBRE . ". Su predio se encuentra en la dirección " . $persona->DIRECCION . " - " . $persona->MUNICIPIO . " y su número de teléfono registrado es " . $persona->TELEFONO . ".";
            } else {
                //Respuesta para cuando no hay un teléfono registrado
                $json['speech'] = "El nombre del usuario con el número de cuenta " . $persona->NIU . ", es " . $persona->NOMBRE . ". Su predio se encuentra en la dirección " . $persona->DIRECCION . " y no tenemos registrado ningún número telefónico.";
                $json['displayText'] = "El nombre del usuario con el número de cuenta " . $persona->NIU . ", es " . $persona->NOMBRE . ". Su predio se encuentra en la dirección " . $persona->DIRECCION . " y no tenemos registrado ningún número telefónico.";
            }
            $indispMsg = $this->getIndisponibilidad($persona->NIU);
            $json['speech'] = $json['speech'] . $indispMsg;
            $json['displayText'] = $json['displayText'] . $indispMsg;
        }
        return $json;
    }

    public function respuesta_plural($personas, $context)
    {
        //Verificar si se encontró alguna cuenta con el nombre asociado
        if (is_null($personas) || count($personas) == 0) {
            //Respuesta para cuando no se encuentra la cuenta con el nombre asociado
            $json['speech'] = "No se ha encontrado ninguna cuenta con el dato ingresado.";
            $json['displayText'] = "No se ha encontrado ninguna cuenta con el dato ingresado.";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => "No se ha encontrado ninguna cuenta con el dato ingresado. \n ¿Deseas consultar algo más?",
                            'reply_markup' => array(
                                'keyboard' => array(
                                    array(
                                        array(
                                            'text' => 'Sí ✔️',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => 'No ❌',
                                            'callback_data' => 'No',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            );

        } else {
            $json['speech'] = "Hemos encontrado las siguientes cuentas asociadas con el dato dado (Si su cuenta no se encuentra entre los resultados, intente con un criterio de búsqueda más específico)";
            $json['displayText'] = "Hemos encontrado las siguientes cuentas asociadas con el dato dado. (Si su cuenta no se encuentra entre los resultados, intente con un criterio de búsqueda más específico)\n";
            foreach ($personas as $persona) {
                $json['speech'] .= "\n - Nombre: " . $persona->NOMBRE . "\n - Dirección: " . $persona->DIRECCION . "\n - Numero de cuenta: " . $persona->NIU;
                $json['displayText'] .= "---------------\n\n - Nombre: " . $persona->NOMBRE . "\n - Dirección: " . $persona->DIRECCION . "\n - Numero de cuenta: " . $persona->NIU;
            }
            $json['speech'] .= "\n A continuación, digita el número de cuenta correspondiente a tu solicitud";
            $json['displayText'] .= "\n A continuación, digita el número de cuenta correspondiente a tu solicitud";

            if ($context == "c1") {
                $json['contextOut'] = array(
                    array("name" => "c1_niu", "parameters" => array("res" => "1"), "lifespan" => 4),
                    array("name" => "c1_cc", "parameters" => array("res" => "1"), "lifespan" => 4));
            }
            if ($context == "c2") {
                $json['contextOut'] = array(array('name' => 'c2_niu', 'lifespan' => 4, 'parameters' => json_decode("{}")));
            }

        }
        return $json;
    }

    //Obtener los datos del usuario a partir del NIU
    //Todas estas tienden a desaparecer en el update
    public function getUserData($NIU)
    {
        $persona = getData($NIU, $this->con);
        return $this->respuesta($persona);
    }

    public function getNiuFromCedula($cedula)
    {
        $personas = getNIUwithCedula($this->con, $cedula);
        $resultado = array();
        //Si encuentra un solo registro
        if (count($personas) == 1) {
            $resultado['NIU'] = $personas[0]->NIU;
        } elseif (count($personas) > 1) {
            $foundResults = array();
            foreach ($personas as $key => $value) {
                array_push($foundResults, array('NIU' => $value->NIU, 'DIRECCION' => $value->DIRECCION));
            }
            $resultado['VARIOS'] = $foundResults;

        } else {
            $resultado['NINGUNO'] = 1;
        }
        return $resultado;
    }

    public function getNiuFromNIT($nit, $context)
    {
        $personas = getNIUwithNIT($this->con, $nit);
        $resultado = array();
        //Si encuentra un solo registro
        if (count($personas) == 1) {
            $resultado['NIU'] = $personas[0]->NIU;
        } elseif (count($personas) > 1) {
            $foundResults = array();
            foreach ($personas as $key => $value) {
                array_push($foundResults, array('NIU' => $value->NIU, 'DIRECCION' => $value->DIRECCION));
            }
            $resultado['VARIOS'] = $foundResults;

        } else {
            $resultado['NINGUNO'] = 1;
        }
        return $resultado;
    }

    public function getNiuFromName($nombre, $context)
    {
        $palabras = explode(" ", strtoupper($nombre));
        $personas = getNIUwithName($this->con, $palabras);
        return $this->respuesta_plural($personas, $context);
    }

    public function getNiuFromTelephone($telefono, $context)
    {
        $persona = getNIUwithTel($this->con, $telefono);
        return $this->respuesta_plural($persona, $context);
    }

    public function getNiuFromAddress($direccion)
    {
        $direccionesProcesadas = $this->processAddress($direccion);
        $personas = getNIUwithAddress($this->con, $direccionesProcesadas);
        $resultado = array();
        //Si encuentra un solo registro
        if (count($personas) == 1) {
            $resultado['NIU'] = $personas[0]->NIU;

        } elseif (count($personas) > 1) {
            $foundResults = array();
            foreach ($personas as $key => $value) {
                array_push($foundResults, array('NIU' => $value->NIU, 'DIRECCION' => $value->DIRECCION));
            }
            $resultado['VARIOS'] = $foundResults;

        } else {
            $resultado['NINGUNO'] = 1;
        }
        return $resultado;
    }

    public function processAddress($direccion)
    {
        $direcNoSymbols = $direccion;
        $direcNoHyphens = $direccion;
        if (strpos($direccion, '#')) {
            $direcNoSymbols = substr_replace($direccion, ' ', strpos($direccion, '#'), 1);
        }
        if (strpos($direccion, '-')) {
            $direcNoHyphens = substr_replace($direcNoSymbols, ' ', strpos($direcNoSymbols, '-'), 1);
        }
        $output = preg_replace('!\s+!', ' ', $direcNoHyphens);
        $array = explode(" ", strtoupper($output));
        foreach ($array as $i => $value) {
            if ($value == "CARRERA" || $value == "CRA" || $value == "CAR" || $value == "CR") {
                $array[$i] = "CRA";
            }
            if ($value == "CALLE" || $value == "CLL" || $value == "CALL" || $value == "CALL") {
                $array[$i] = "CLL";
            }
            if ($value == "AVENIDA" || $value == "AV" || $value == "AVE" || $value == "AVDA") {
                $array[$i] = "AVE";
                array_push($array, "AV");
                array_push($array, "AVDA");
            }
            if ($value == "APARTAMENTO" || $value == "APTO" || $value == "AP") {
                $array[$i] = "APT";
                array_push($array, "APTO");
            }
            if ($value == "BLOQUE" || $value == "BLQ" || $value == "BL" || $value == "BLOKE") {
                $array[$i] = "BLQ";
                array_push($array, "BLO");
            }
            if ($value == "LOCAL" || $value == "LOC") {
                $array[$i] = "LOC";
            }
            if ($value == "VEREDA" || $value == "VDA") {
                $array[$i] = "VDA";
            }
            if ($value == "SECTOR" || $value == "SEC" || $value == "SECT") {
                $array[$i] = "SECTOR";
                array_push($array, "SEC");
                array_push($array, "SECT");
            }
        }
        return $array;
    }

    //---------------------------- MÉTODOS INVOCADOS DESDE ENDPOINT ------------------------------

    //c1_niu
    //Método que busca las indisponibilidades teniendo el numero de cuenta
    public function getIndisNiu($niu)
    {
        $json['speech'] = $this->getIndisponibilidad($niu);
        $json['displayText'] = $this->getIndisponibilidad($niu);
        $json['messages'] = array(
            array(
                'type' => 4,
                'platform' => 'telegram',
                'payload' => array(
                    'telegram' => array(
                        'text' => $this->getIndisponibilidad($niu) . "\n ¿Deseas consultar algo más?",
                        'reply_markup' => array(
                            'keyboard' => array(
                                array(
                                    array(
                                        'text' => 'Sí ✔️',
                                        'callback_data' => 'Menú Principal',
                                    ),
                                ),
                                array(
                                    array(
                                        'text' => 'No ❌',
                                        'callback_data' => 'No',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
        return $json;
    }

    //c1_direccion_municipio
    //Método que busca el NIU de un usuario asociado con su direccion. Puede encontrar 1 solo registro y buscar, 2 o mas y mostrar una
    //lista de posibles nius encontrados, o indicar que no se encontro registro alguno.
    public function getIndisAddress($direccion)
    {
        $busqueda = $this->getNiuFromAddress($direccion);
        //Verificar si se obtuvo una sola cuenta
        if (isset($busqueda['NIU'])) {
            return $this->getIndisNiu($busqueda['NIU']);
        }

        //Verificar si se obtuvo más de una dirección
        if (isset($busqueda['VARIOS'])) {
            $json['speech'] = "Encontramos las siguientes cuentas asociadas a la dirección buscada: \n ";
            $json['displayText'] = "Encontramos las siguientes cuentas asociadas a la dirección buscada: \n ";
            foreach ($busqueda['VARIOS'] as $key => $value) {
                $json['speech'] .= "- Dirección: " . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
                $json['displayText'] .= "- Dirección:" . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
            }
            $json['speech'] .= "A continuación, ingresa el número de cuenta que deseas consultar.";
            $json['displayText'] .= "A continuación, ingresa el número de cuenta que deseas consultar.";
            $json['contextOut'] = array(
                array("name" => "c1_niu", "parameters" => array("res" => "1"), "lifespan" => 4));
            return $json;
        }

        //Verificar si no se encontró ninguna dirección
        if (isset($busqueda['NINGUNO'])) {
            $json['speech'] = "No he podido encontrar ningún registro asociado con esta dirección.";
            $json['displayText'] = "No he podido encontrar ningún registro asociado con esta dirección.";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => "No he podido encontrar ningún registro asociado con esta dirección. \n ¿Deseas consultar algo más?",
                            'reply_markup' => array(
                                'keyboard' => array(
                                    array(
                                        array(
                                            'text' => 'Sí ✔️',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => 'No ❌',
                                            'callback_data' => 'No',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $json;
        }
    }

    //c1_cc
    // Funcion para buscar Indisponibilidad con la cedula
    public function getIndisCC($cedula)
    {
        $busqueda = $this->getNiuFromCedula($cedula);
        //Verificar si se obtuvo una sola cuenta
        if (isset($busqueda['NIU'])) {
            return $this->getIndisNiu($busqueda['NIU']);
        }

        //Verificar si se obtuvo más de una dirección
        if (isset($busqueda['VARIOS'])) {
            $json['speech'] = "Encontramos las siguientes cuentas asociadas a la cédula buscada: \n ";
            $json['displayText'] = "Encontramos las siguientes cuentas asociadas a la cédula buscada: \n ";
            foreach ($busqueda['VARIOS'] as $key => $value) {
                $json['speech'] .= "- Dirección: " . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
                $json['displayText'] .= "- Dirección:" . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
            }
            $json['speech'] .= "A continuación, ingresa el número de cuenta que deseas consultar.";
            $json['displayText'] .= "A continuación, ingresa el número de cuenta que deseas consultar.";
            $json['contextOut'] = array(
                array("name" => "c1_niu", "parameters" => array("res" => "1"), "lifespan" => 4));
            return $json;
        }

        //Verificar si no se encontró ninguna dirección
        if (isset($busqueda['NINGUNO'])) {
            $json['speech'] = "No he podido encontrar ningún registro asociado con esta cédula.";
            $json['displayText'] = "No he podido encontrar ningún registro asociado con esta cédula.";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => "No he podido encontrar ningún registro asociado con esta cédula.\n ¿Deseas consultar algo más?",
                            'reply_markup' => array(
                                'keyboard' => array(
                                    array(
                                        array(
                                            'text' => 'Sí ✔️',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => 'No ❌',
                                            'callback_data' => 'No',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $json;
        }
    }

    //c1_nit
    // Funcion para buscar Indisponibilidad con el NIT
    public function getIndisNIT($cedula)
    {
        $busqueda = $this->getNiuFromNIT($cedula);
        //Verificar si se obtuvo una sola cuenta
        if (isset($busqueda['NIU'])) {
            return $this->getIndisNiu($busqueda['NIU']);
        }

        //Verificar si se obtuvo más de una dirección
        if (isset($busqueda['VARIOS'])) {
            $json['speech'] = "Encontramos las siguientes cuentas asociadas a la dirección buscada: \n ";
            $json['displayText'] = "Encontramos las siguientes cuentas asociadas a la dirección buscada: \n ";
            foreach ($busqueda['VARIOS'] as $key => $value) {
                $json['speech'] .= "- Dirección: " . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
                $json['displayText'] .= "- Dirección:" . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
            }
            $json['speech'] .= "A continuación, ingresa el número de cuenta que deseas consultar.";
            $json['displayText'] .= "A continuación, ingresa el número de cuenta que deseas consultar.";
            $json['contextOut'] = array(
                array("name" => "c1_niu", "parameters" => array("res" => "1"), "lifespan" => 4));
            return $json;
        }

        //Verificar si no se encontró ninguna dirección
        if (isset($busqueda['NINGUNO'])) {
            $json['speech'] = "No he podido encontrar ningún registro asociado con esta cédula.";
            $json['displayText'] = "No he podido encontrar ningún registro asociado con esta cédula.";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => "\n ¿Deseas consultar algo más?",
                            'reply_markup' => array(
                                'keyboard' => array(
                                    array(
                                        array(
                                            'text' => 'Sí ✔️',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => 'No ❌',
                                            'callback_data' => 'No',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $json;
        }
    }

    //c2_niu
    //Método que busca las suspensiones programada teniendo el numero de cuenta
    public function getSPNiu($niu)
    {
        $json['speech'] = $this->getSuspensionesProgramadas($niu, true);
        $json['displayText'] = $this->getSuspensionesProgramadas($niu, true);
        $json['messages'] = array(
            array(
                'type' => 4,
                'platform' => 'telegram',
                'payload' => array(
                    'telegram' => array(
                        'text' => $this->getSuspensionesProgramadas($niu, true) . "\n ¿Deseas consultar algo más?",
                        'reply_markup' => array(
                            'keyboard' => array(
                                array(
                                    array(
                                        'text' => 'Sí ✔️',
                                        'callback_data' => 'Menú Principal',
                                    ),
                                ),
                                array(
                                    array(
                                        'text' => 'No ❌',
                                        'callback_data' => 'No',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
        return $json;
    }

    //-----------------------------------------------------------------------------



    

    // ------------------------------- MAIN C1 ----------------------------------
    //método que obtiene las indisponibilidades con el NIU. Se diferencia de getIndisNiu, en cuanto a que esta
    //puede ser reutilizada en otros parametros
    public function getIndisponibilidad($niu)
    {

        $susp = getSuspEfectiva($this->con, $niu);

        $msg = "";

        if (count($susp) > 0) {
            if ($susp->VALOR == "s") {
                $msg .= "\n🔷 Para esta cuenta, se reporta una suspensión efectiva por falta de pago realizada en la siguiente fecha: " . $susp->HORA_FIN;
                return $msg;
            } else {
                //Invocar metodo para buscar interrupcion programada
                return $this->getSuspensionesProgramadas($niu, false);
            }

        } else {
            //Invocar metodo para buscar interrupcion programada
            return $this->getSuspensionesProgramadas($niu, false);
        }

    }

    //Método que obtiene las suspensiones programadas teniendo el NIU. Reutilizable
    public function getSuspensionesProgramadas($niu, $soloC2)
    {
        $prog = getSuspProgramada($this->con, $niu);
        //var_dump($prog);
        $msg = "";
        if (count($prog) > 0) {
            $msg .= "\n🔷 Para esta cuenta, hemos encontrado las siguientes suspensiones programadas: ";
            foreach ($prog as $p) {
                $msg .= "\n🔷 Hay una suspensión programada que inicia el " . $p->FECHA_INICIO . " a las " . $p->HORA_INICIO . ", y finaliza el " . $p->FECHA_FIN . " a las " . $p->HORA_FIN;
            }
            return $msg;
        } else {
            if ($soloC2) {
                $msg .= "\nTe cuento, en el momento no registras ninguna interrupción programada 👍⚡ \nSi deseas más información al respecto te tenemos los siguientes canales: \n🔹 Línea para trámites y solicitudes: Marca 01 8000 912432 #415 \n🔹 Línea para daños: Marca 115.\n";
                return $msg;

            } else {
                //Invocar metodo para buscar interr scada
                return $this->getInterrupCircuito($niu);
            }

        }
    }

    //Método que obtiene las interrupciones a nivel de circuito SCADA teniendo el NIU. Reutilizable
    public function getInterrupCircuito($niu)
    {
        $circuito = getSuspCircuito($this->con, $niu);
        $msg = "";
        if (count($circuito) > 0 && ($circuito->ESTADO == "ABIERTO" || $circuito->ESTADO == "APERTURA")) {

            $msg .= "\n🔷 Para esta cuenta, hemos encontrado las siguientes indisponibilidades a nivel de circuito: \n🔷 Hay una falla en el circuito reportada el " . $circuito->FECHA . " a las " . $circuito->HORA . ". Estamos trabajando para reestablecer el servicio";
            return $msg;
        } else {
            //Aqui se debe invocar la busqueda en SGO
            return "\nTe cuento, en el momento no registras ninguna interrupción en el servicio de energía 👍⚡ \nSi deseas más información al respecto te tenemos los siguientes canales: \n🔹 Línea para trámites y solicitudes: Marca 01 8000 912432 #415 \n🔹 Línea para daños: Marca 115.\n";
        }
    }
    //-----------------------------------------------------------------------------





    //----------------------------CARGA DE DATOS------------------------------------
    //Carga de indisponibilidad a nivel de circuito
    public function setIndispCircuito($data)
    {
        $result = insertIndispCircuito($this->con, $data);
        return $result;
    }

    //Auxiliar de carga de indisponibilidad a nivel de circuito
    public function getIndisponibilidadCircuitoData($cadena)
    {
        $array = explode(" ", strtoupper($cadena));
        $response['FECHA'] = $array[1];
        $response['HORA'] = $array[2];
        $response['ESTADO'] = $array[3];
        $response['CIRCUITO'] = $array[4];
        return $response;
    }

    //Carga de suspensiones efectivas
    public function setSuspensionEfectiva($data)
    {

        $result = insertSuspensionesEfectivas($this->con, $data);
        return $result;
    }

    //Carga de suspensiones programadas
    public function setSuspProgramada($data)
    {
        $result = insertSuspProgramada($this->con, $data);
        return $result;
    }

    //Actualizacion de suspensiones programadas
    public function updateSuspProgramada($data)
    {
        $result = updSuspProgramada($this->con, $data);
        return $result;
    }
    //----------------------------------------------------------------------

}
