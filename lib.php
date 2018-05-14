<?php
require './consultas.php';
require "Mesmotronic/Soap/WsaSoap.php";
require "Mesmotronic/Soap/WsaSoapClient.php";
require "Mesmotronic/Soap/WsseAuthHeader.php";

class chatBotAPI
{

    //Credenciales HEROKU Mlab
    private $host = "mongodb://heroku_69tb2th4:m2oheamen7422pmnq3htdb56dt@ds113775.mlab.com:13775/heroku_69tb2th4";

    private $SGOurl = 'https://checindisponibilidad.chec.com.co/ServiceIndisponibilidad.svc?wsdl';

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
                //Mostrar valores enmascarados
                /* $direcShow = "******".substr( $value->DIRECCION, -7);
                array_push($foundResults, array('NIU' => $value->NIU, 'DIRECCION' => $direcShow)); */

                //Mostrar valores sin enmascarar
                array_push($foundResults, array('NIU' => $value->NIU, 'DIRECCION' => $value->DIRECCION));
            }
            $resultado['VARIOS'] = $foundResults;

        } else {
            $resultado['NINGUNO'] = 1;
        }
        return $resultado;
    }

    public function getNiuFromNIT($nit)
    {
        $personas = getNIUwithNIT($this->con, $nit);
        $resultado = array();
        //Si encuentra un solo registro
        if (count($personas) == 1) {
            $resultado['NIU'] = $personas[0]->NIU;
        } elseif (count($personas) > 1) {
            $foundResults = array();
            foreach ($personas as $key => $value) {
                //Mostrar valores enmascarados
                /* $direcShow = "******".substr( $value->DIRECCION, -7);
                array_push($foundResults, array('NIU' => $value->NIU, 'DIRECCION' => $direcShow)); */

                //Mostrar valores sin enmascarar
                array_push($foundResults, array('NIU' => $value->NIU, 'DIRECCION' => $value->DIRECCION));
            }
            $resultado['VARIOS'] = $foundResults;

        } else {
            $resultado['NINGUNO'] = 1;
        }
        return $resultado;
    }


    public function getNiuFromAddress($direccion, $municipio)
    {
        $direccionesProcesadas = $this->processAddress($direccion);
        $personas = getNIUwithAddress($this->con, $direccionesProcesadas, $municipio);
        $resultado = array();
        //Si encuentra un solo registro
        if (count($personas) == 1) {
            $resultado['NIU'] = $personas[0]->NIU;

        } elseif (count($personas) > 1) {
            $foundResults = array();
            foreach ($personas as $key => $value) {

                //La siguiente línea se encarga de enmascarar los datos por motivos de seguridad.
                /* $direcShow = "******".substr( $value->DIRECCION, -7);
                array_push($foundResults, array('NIU' => $value->NIU, 'DIRECCION' =>$direcShow)); */
                
                //Sin enmascarar
                array_push($foundResults, array('NIU' => $value->NIU, 'DIRECCION' =>$value->DIRECCION));
            }
            $resultado['VARIOS'] = $foundResults;

        } else {
            $resultado['NINGUNO'] = 1;
        }
        return $resultado;
    }

    public function getNiuFromName($nombre, $municipio)
    {
        $personas = getNIUwithName($this->con, $nombre, $municipio);
        $resultado = array();
        //Si encuentra un solo registro
        if (count($personas) == 1) {
            $resultado['NIU'] = $personas[0]->NIU;

        } elseif (count($personas) > 1) {
            $foundResults = array();
            foreach ($personas as $key => $value) {

                //Mostrar valores enmascarados
                /* $direcShow = "******".substr( $value->DIRECCION, -7);
                array_push($foundResults, array('NIU' => $value->NIU, 'DIRECCION' => $direcShow)); */

                //Mostrar valores sin enmascarar
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
        if (strpos($direccion, '#')) {
            $direcNoSymbols = substr_replace($direccion, ' ', strpos($direccion, '#'), 1);
        }
        if (strpos($direccion, '-')) {
            $direcNoSymbols = substr_replace($direcNoSymbols, ' ', strpos($direcNoSymbols, '-'), 1);
        }
        $res = preg_replace("/[^a-zA-Z0-9\s]/", "", $direcNoSymbols);
        $output = preg_replace('!\s+!', ' ', $res);
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
                //array_push($array, "AV");
                //array_push($array, "AVDA");
            }
            if ($value == "APARTAMENTO" || $value == "APTO" || $value == "AP") {
                $array[$i] = "APT";
            }
            if ($value == "BLOQUE" || $value == "BLQ" || $value == "BL" || $value == "BLOKE") {
                $array[$i] = "BLQ";
                //array_push($array, "BLO");
            }
            if ($value == "LOCAL" || $value == "LOC") {
                $array[$i] = "LOC";
            }
            if ($value == "VEREDA" || $value == "VDA") {
                $array[$i] = "VDA";
            }
            if ($value == "SECTOR" || $value == "SEC" || $value == "SECT") {
                $array[$i] = "SECTOR";
                //array_push($array, "SEC");
                //array_push($array, "SECT");
            }
        }
        return $array;
    }

    //---------------------------- MÉTODOS INVOCADOS DESDE ENDPOINT ------------------------------

    //c1_niu
    //Método que busca las indisponibilidades teniendo el numero de cuenta
    public function getIndisNiu($niu)
    {
        $response = $this->getIndisponibilidad($niu);

        //Validar si no se encontró ninguna indisponibilidad para enviar diferentes tipos de respuesta
        if(substr($response, 0, 7)=="En este"){
            $json['speech'] = $response;
            $json['displayText'] = $response;
            $json['messages'] = array(
                array(
                    'type' => 0,
                    'platform' => 'telegram',
                    'speech' =>  $response
                ),
                array(
                    'type' => 0,
                    'platform' => 'telegram',
                    'speech' =>  "\n🔹 Línea para trámites y solicitudes: Marca 01 8000 912432 #415 \n🔹 Línea para daños: Marca 115.\n🔹 CHAT en Linea: "
                ),
                array(
                    'type' => 1,
                    'platform' => 'telegram',
                    'title' => 'Chat asistido por agente',
                    'subtitle' => '',
                    'buttons' => array(
                        array(
                            'text' => "👆 Ingresa Aquí",
                            'postback' => "https://servicio.asistenciachat.com/website/chec_chat/Default2.aspx"
                        )
                    )
                ),
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' =>  "Si por el contrario deseas seguir conversando conmigo selecciona una de las siguientes opciones:",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '👌 He finalizado la consulta',
                                            'callback_data' => 'No',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => $response
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "🔹 Línea para trámites y solicitudes: Marca 01 8000 912432 #415"
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "🔹 Línea para daños: Marca 115."
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "🔹 CHAT en Línea: "
                ),
                array(
                    'type' => 1,
                    'platform' => 'skype',
                    'title' => 'Chat asistido por agente',
                    'subtitle' => "",
                    'buttons' => array(
                        array(
                            'text' => "Ingresa Aquí",
                            'postback' => "https://servicio.asistenciachat.com/website/chec_chat/Default2.aspx"
                        )
                    )
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "Si por el contrario deseas seguir conversando conmigo selecciona una de las siguientes opciones:"
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción:',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal',
                        '👌 He finalizado la consulta'
                    )
                )
            );
        }else{ //Se envía cuando sí se encuentra alguna indisponibilidad
            $json['speech'] = $response . "A continuación selecciona una opción:";
            $json['displayText'] = $response . "\n A continuación selecciona una opción:";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => $response . "\n A continuación selecciona una opción:",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '👌 He finalizado la consulta',
                                            'callback_data' => 'No',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => $response
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción:',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal',
                        '👌 He finalizado la consulta'
                    )
                )
            );
        }

        return $json;
    }

    //c1_direccion_municipio
    //Método que busca el NIU de un usuario asociado con su direccion. Puede encontrar 1 solo registro y buscar, 2 o mas y mostrar una
    //lista de posibles nius encontrados, o indicar que no se encontro registro alguno.
    public function getIndisAddress($direccion, $municipio)
    { 
        $busqueda = $this->getNiuFromAddress($direccion, $municipio);
        //Verificar si se obtuvo una sola cuenta
        if (isset($busqueda['NIU'])) {
            return $this->getIndisNiu($busqueda['NIU']);
        }

        //Verificar si se obtuvo más de una dirección
        if (isset($busqueda['VARIOS'])) {
            $json['speech'] = "Encontramos las siguientes cuentas asociadas a la dirección buscada (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            $json['displayText'] = "Encontramos las siguientes cuentas asociadas a la dirección buscada (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            foreach ($busqueda['VARIOS'] as $key => $value) {
                $json['speech'] .= "- Dirección: " . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
                $json['displayText'] .= "- Dirección:" . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
            }
            $json['speech'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda";
            $json['displayText'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda\n Si por el contrario, quieres buscar por otra opción escribe 'Buscar de nuevo'\n Si quieres regresar al menú escribe 'Menu Principal'";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => $json['speech']."\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees.",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '🔍 Consulta el número de cuenta',
                                            'callback_data' => 'NIU',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => $json['speech'] . "\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees."
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal',
                        '🔍 Consulta el número de cuenta'
                    )
                )
            );
            return $json;
        }

        //Verificar si no se encontró ninguna dirección
        if (isset($busqueda['NINGUNO'])) {
            $json['speech'] = "No encuentro ningún registro asociado a esta dirección. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['displayText'] = "No encuentro ningún registro asociado a esta dirección. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => "No encuentro ningún registro asociado a esta dirección. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "No encuentro ningún registro asociado a esta dirección. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal"
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal'
                    )
                )
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
            $json['speech'] = "Encontramos las siguientes cuentas asociadas a la cédula buscada (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            $json['displayText'] = "Encontramos las siguientes cuentas asociadas a la cédula buscada (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            foreach ($busqueda['VARIOS'] as $key => $value) {
                $json['speech'] .= "- Dirección: " . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
                $json['displayText'] .= "- Dirección:" . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
            }
            $json['speech'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda";
            $json['displayText'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda\n Si por el contrario, quieres buscar por otra opción escribe 'Buscar de nuevo'\n Si quieres regresar al menú escribe 'Menu Principal'";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => $json['speech']."\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees.",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '🔍 Consulta el número de cuenta',
                                            'callback_data' => 'NIU',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => $json['speech'] . "\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees."
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal',
                        '🔍 Consulta el número de cuenta'
                    )
                )
            );
            return $json;
        }

        //Verificar si no se encontró ninguna cédula
        if (isset($busqueda['NINGUNO'])) {
            $json['speech'] = "No encuentro ningún registro asociado a este número de cédula. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['displayText'] = "No encuentro ningún registro asociado a este número de cédula. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => "No encuentro ningún registro asociado a este número de cédula. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "No encuentro ningún registro asociado a este número de cédula. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal"
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal'
                    )
                )
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
            $json['speech'] = "Encontramos las siguientes cuentas asociadas al NIT buscado (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            $json['displayText'] = "Encontramos las siguientes cuentas asociadas al NIT buscado (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            foreach ($busqueda['VARIOS'] as $key => $value) {
                $json['speech'] .= "- Dirección: " . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
                $json['displayText'] .= "- Dirección:" . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
            }
            $json['speech'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda";
            $json['displayText'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda";
            $json['displayText'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda\n Si por el contrario, quieres buscar por otra opción escribe 'Buscar de nuevo'\n Si quieres regresar al menú escribe 'Menu Principal'";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => $json['speech']."\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees.",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '🔍 Consulta el número de cuenta',
                                            'callback_data' => 'NIU',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => $json['speech'] . "\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees."
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal',
                        '🔍 Consulta el número de cuenta'
                    )
                )
            );
            return $json;
        }

        //Verificar si no se encontró ninguna dirección
        if (isset($busqueda['NINGUNO'])) {
            $json['speech'] = "No encuentro ningún registro asociado a este número de NIT. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['displayText'] = "No encuentro ningún registro asociado a este número de NIT. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => "No encuentro ningún registro asociado a este número de NIT. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "No encuentro ningún registro asociado a este número de NIT. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal"
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal'
                    )
                )
            );
            return $json;
        }
    }

    //c1_nombre
    // Funcion para buscar Indisponibilidad con el Nombre
    public function getIndisNombre($nombre, $municipio)
    {
        $palabras = explode(" ", strtoupper($nombre));
        $busqueda = $this->getNiuFromName($palabras, $municipio);
        //Verificar si se obtuvo una sola cuenta
        if (isset($busqueda['NIU'])) {
            return $this->getIndisNiu($busqueda['NIU']);
        }

        //Verificar si se obtuvo más de una dirección
        if (isset($busqueda['VARIOS'])) {
            $json['speech'] = "Encontramos las siguientes cuentas asociadas al nombre buscado (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            $json['displayText'] = "Encontramos las siguientes cuentas asociadas al nombre buscado (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            foreach ($busqueda['VARIOS'] as $key => $value) {
                $json['speech'] .= "- Dirección: " . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
                $json['displayText'] .= "- Dirección:" . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
            }
            $json['speech'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda";
            $json['displayText'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda\n Si por el contrario, quieres buscar por otra opción escribe 'Buscar de nuevo'\n Si quieres regresar al menú escribe 'Menu Principal'";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => $json['speech']."\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees.",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '🔍 Consulta el número de cuenta',
                                            'callback_data' => 'NIU',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => $json['speech'] . "\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees."
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal',
                        '🔍 Consulta el número de cuenta'
                    )
                )
            );
            return $json;
        }

        //Verificar si no se encontró ninguna dirección
        if (isset($busqueda['NINGUNO'])) {
            $json['speech'] = "No encuentro ningún registro asociado a este nombre. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['displayText'] = "No encuentro ningún registro asociado a este nombre. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => "No encuentro ningún registro asociado a este nombre. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "No encuentro ningún registro asociado a este nombre. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal"
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal'
                    )
                )
            );
            return $json;
        }
    }

    //c2_niu
    //Método que busca las suspensiones programada teniendo el numero de cuenta
    public function getSPNiu($niu)
    {
        $response = $this->getSuspensionesProgramadas($niu, true);
         //Validar si no se encontró ninguna susp programada para enviar diferentes tipos de respuesta
         if(substr($response, 0, 7)=="En este"){
            $json['speech'] = $response;
            $json['displayText'] = $response;
            $json['messages'] = array(
                array(
                    'type' => 0,
                    'platform' => 'telegram',
                    'speech' =>  $response
                ),
                array(
                    'type' => 0,
                    'platform' => 'telegram',
                    'speech' =>  "\n🔹 Línea para trámites y solicitudes: Marca 01 8000 912432 #415 \n🔹 Línea para daños: Marca 115.\n🔹 CHAT en Linea: "
                ),
                array(
                    'type' => 1,
                    'platform' => 'telegram',
                    'title' => 'Chat asistido por agente',
                    'subtitle' => '',
                    'buttons' => array(
                        array(
                            'text' => "👆 Ingresa Aquí",
                            'postback' => "https://servicio.asistenciachat.com/website/chec_chat/Default2.aspx"
                        )
                    )
                ),
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' =>  "Si por el contrario deseas seguir conversando conmigo selecciona una de las siguientes opciones:",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '👌 He finalizado la consulta',
                                            'callback_data' => 'No',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => $response
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "🔹 Línea para trámites y solicitudes: Marca 01 8000 912432 #415"
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "🔹 Línea para daños: Marca 115."
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "🔹 CHAT en Línea: "
                ),
                array(
                    'type' => 1,
                    'platform' => 'skype',
                    'title' => 'Chat asistido por agente',
                    'subtitle' => "",
                    'buttons' => array(
                        array(
                            'text' => "Ingresa Aquí",
                            'postback' => "https://servicio.asistenciachat.com/website/chec_chat/Default2.aspx"
                        )
                    )
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "Si por el contrario deseas seguir conversando conmigo selecciona una de las siguientes opciones:"
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción:',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal',
                        '👌 He finalizado la consulta'
                    )
                )
            );
        }else{ //Se envía cuando sí se encuentra alguna indisponibilidad
            $json['speech'] = $response . "A continuación selecciona una opción:";
            $json['displayText'] = $response . "\n A continuación selecciona una opción:";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => $response . "\n A continuación selecciona una opción:",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '👌 He finalizado la consulta',
                                            'callback_data' => 'No',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => $response
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción:',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal',
                        '👌 He finalizado la consulta'
                    )
                )
            );
        }

        return $json;
    }

    //c2_cc
    // Funcion para buscar Indisponibilidad con la cedula
    public function getSPCC($cedula)
    {
        $busqueda = $this->getNiuFromCedula($cedula);
        //Verificar si se obtuvo una sola cuenta
        if (isset($busqueda['NIU'])) {
            return $this->getSPNiu($busqueda['NIU']);
        }

        //Verificar si se obtuvo más de una dirección
        if (isset($busqueda['VARIOS'])) {
            $json['speech'] = "Encontramos las siguientes cuentas asociadas a la cédula buscada (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            $json['displayText'] = "Encontramos las siguientes cuentas asociadas a la cédula buscada (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            foreach ($busqueda['VARIOS'] as $key => $value) {
                $json['speech'] .= "- Dirección: " . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
                $json['displayText'] .= "- Dirección:" . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
            }
            $json['speech'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda";
            $json['displayText'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda\n Si por el contrario, quieres buscar por otra opción escribe 'Buscar de nuevo'\n Si quieres regresar al menú escribe 'Menu Principal'";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => $json['speech']."\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees.",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar otra vez',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '🔍 Consulta el número de cuenta',
                                            'callback_data' => 'NIU',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => $json['speech'] . "\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees."
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal',
                        '🔍 Consulta el número de cuenta'
                    )
                )
            );
            return $json;
        }

        //Verificar si no se encontró ninguna dirección
        if (isset($busqueda['NINGUNO'])) {
            $json['speech'] = "No encuentro ningún registro asociado a este número de cédula. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal?";
            $json['displayText'] = "No encuentro ningún registro asociado a este número de cédula. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal?";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => "No encuentro ningún registro asociado a este número de cédula. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal?",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "No encuentro ningún registro asociado a este número de cédula. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal"
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal'
                    )
                )
            );
            return $json;
        }
    }

     //c2_nit
    // Funcion para buscar Indisponibilidad con el NIT
    public function getSPNIT($cedula)
    {
        $busqueda = $this->getNiuFromNIT($cedula);
        //Verificar si se obtuvo una sola cuenta
        if (isset($busqueda['NIU'])) {
            return $this->getSPNiu($busqueda['NIU']);
        }

        //Verificar si se obtuvo más de una dirección
        if (isset($busqueda['VARIOS'])) {
            $json['speech'] = "Encontramos las siguientes cuentas asociadas al NIT buscado (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            $json['displayText'] = "Encontramos las siguientes cuentas asociadas al NIT buscado (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            foreach ($busqueda['VARIOS'] as $key => $value) {
                $json['speech'] .= "- Dirección: " . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
                $json['displayText'] .= "- Dirección:" . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
            }
            $json['speech'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda";
            $json['displayText'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda\n Si por el contrario, quieres buscar por otra opción escribe 'Buscar de nuevo'\n Si quieres regresar al menú escribe 'Menu Principal'";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => $json['speech']."\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees.",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar otra vez',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '🔍 Consulta el número de cuenta',
                                            'callback_data' => 'NIU',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => $json['speech'] . "\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees."
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal',
                        '🔍 Consulta el número de cuenta'
                    )
                )
            );
            return $json;
        }

        //Verificar si no se encontró ninguna dirección
        if (isset($busqueda['NINGUNO'])) {
            $json['speech'] = "No encuentro ningún registro asociado a este número de NIT. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['displayText'] = "No encuentro ningún registro asociado a este número de NIT. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => "No encuentro ningún registro asociado a este número de NIT. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "No encuentro ningún registro asociado a este número de NIT. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal"
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal'
                    )
                )
            );
            return $json;
        }
    }

    //c2_direccion_municipio
    //Método que busca el NIU de un usuario asociado con su direccion. Puede encontrar 1 solo registro y buscar, 2 o mas y mostrar una
    //lista de posibles nius encontrados, o indicar que no se encontro registro alguno.
    public function getSPAddress($direccion, $municipio)
    {
        $busqueda = $this->getNiuFromAddress($direccion, $municipio);
        //Verificar si se obtuvo una sola cuenta
        if (isset($busqueda['NIU'])) {
            return $this->getSPNiu($busqueda['NIU']);
        }

        //Verificar si se obtuvo más de una dirección
        if (isset($busqueda['VARIOS'])) {
            $json['speech'] = "Encontramos las siguientes cuentas asociadas a la dirección buscada (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            $json['displayText'] = "Encontramos las siguientes cuentas asociadas a la dirección buscada (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            foreach ($busqueda['VARIOS'] as $key => $value) {
                $json['speech'] .= "- Dirección: " . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
                $json['displayText'] .= "- Dirección:" . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
            }
            $json['speech'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda";
            $json['displayText'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda\n Si por el contrario, quieres buscar por otra opción escribe 'Buscar de nuevo'\n Si quieres regresar al menú escribe 'Menu Principal'";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => $json['speech']."\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees.",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar otra vez',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '🔍 Consulta el número de cuenta',
                                            'callback_data' => 'NIU',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => $json['speech'] . "\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees."
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal',
                        '🔍 Consulta el número de cuenta'
                    )
                )
            );
            return $json;
        }

        //Verificar si no se encontró ninguna dirección
        if (isset($busqueda['NINGUNO'])) {
            $json['speech'] = "No encuentro ningún registro asociado a esta dirección. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['displayText'] = "No encuentro ningún registro asociado a esta dirección. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => "No encuentro ningún registro asociado a esta dirección. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "No encuentro ningún registro asociado a esta dirección. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal"
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal'
                    )
                )
            );
            return $json;
        }
    }

      //c1_nombre
    // Funcion para buscar Indisponibilidad con el Nombre
    public function getSPNombre($nombre, $municipio)
    {
        $palabras = explode(" ", strtoupper($nombre));
        $busqueda = $this->getNiuFromName($palabras, $municipio);
        //Verificar si se obtuvo una sola cuenta
        if (isset($busqueda['NIU'])) {
            return $this->getSPNiu($busqueda['NIU']);
        }

        //Verificar si se obtuvo más de una dirección
        if (isset($busqueda['VARIOS'])) {
            $json['speech'] = "Encontramos las siguientes cuentas asociadas al nombre buscado (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            $json['displayText'] = "Encontramos las siguientes cuentas asociadas al nombre buscado (Por cuestiones de seguridad no mostramos los datos en su totalidad): \n ";
            foreach ($busqueda['VARIOS'] as $key => $value) {
                $json['speech'] .= "- Dirección: " . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
                $json['displayText'] .= "- Dirección:" . $value['DIRECCION'] . " Número de cuenta: " . $value['NIU'] . " \n  ";
            }
            $json['speech'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda";
            $json['displayText'] .= "A continuación, selecciona el botón 'Consulta el número de cuenta' y luego ingresa el número de cuenta correspondiente a tu búsqueda\n Si por el contrario, quieres buscar por otra opción escribe 'Buscar de nuevo'\n Si quieres regresar al menú escribe 'Menu Principal'";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => $json['speech']."\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees.",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar otra vez',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '🔍 Consulta el número de cuenta',
                                            'callback_data' => 'NIU',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => $json['speech'] . "\n Si por el contrario, quieres buscar por otra opción o regresar al menú, presiona el botón que desees."
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal',
                        '🔍 Consulta el número de cuenta'
                    )
                )
            );
            return $json;
        }

        //Verificar si no se encontró ninguna dirección
        if (isset($busqueda['NINGUNO'])) {
            $json['speech'] = "No encuentro ningún registro asociado a este nombre. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['displayText'] = "No encuentro ningún registro asociado a este nombre. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal";
            $json['messages'] = array(
                array(
                    'type' => 4,
                    'platform' => 'telegram',
                    'payload' => array(
                        'telegram' => array(
                            'text' => "No encuentro ningún registro asociado a este nombre. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal",
                            'reply_markup' => array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => '🔙 Buscar de nuevo',
                                            'callback_data' => '1.',
                                        ),
                                    ),
                                    array(
                                        array(
                                            'text' => '💠 Menú Principal',
                                            'callback_data' => 'Menú Principal',
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 0,
                    'platform' => 'skype',
                    'speech' => "No encuentro ningún registro asociado a este nombre. Para realizar una nueva búsqueda presiona 'Buscar de nuevo', de lo contrario regresa al Menú Principal"
                ),
                array(
                    'type' => 2,
                    'platform' => 'skype',
                    'title' => 'Selecciona una opción',
                    'replies' => array(
                        '🔙 Buscar de nuevo',
                        '💠 Menú Principal'
                    )
                )
            );
            return $json;
        }
    }


    //-----------------------------------------------------------------------------





    // ------------------------------- MAIN C1 ----------------------------------
    //método que obtiene las indisponibilidades con el NIU. Se diferencia de getIndisNiu, en cuanto a que esta
    //puede ser reutilizada en otros parametros
    public function getIndisponibilidad($niu)
    {
        $susp = getSuspEfectiva($this->con, $niu);

        $msg = "";

        if (!is_array($susp)) {
            if ($susp->VALOR == "s") {
                $msg .= "\n🔷 Tu servicio se encuentra suspendido desde: $susp->HORA_FIN por cualquiera de los siguientes motivos: \n - Falta de pago \n - Solicitud del cliente \n - Revisión técnica.";
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
        if (!is_array($prog) || count($prog)>0) {
            $msg .= "\n🔷 Para esta cuenta, hemos encontrado las siguientes suspensiones programadas: ";
            foreach ($prog as $p) {
                $msg .= "\nPara el inmueble consultado encontre las siguientes interrupciones del servicio de energía programadas:\n🔷 Hay una interrupción programada que inicia el " . $p->FECHA_INICIO . " a las " . $p->HORA_INICIO . ", y finaliza el " . $p->FECHA_FIN . " a las " . $p->HORA_FIN;
            }
            return $msg;
        } else {
            if ($soloC2) {
                $msg .= "\nEn este momento no me reporta ninguna falla del servicio en tu sector, por favor comunicate con nosotros CHAT en Linea: \n🔹 Línea para trámites y solicitudes: Marca 01 8000 912432 #415 \n🔹 Línea para daños: Marca 115.\n";
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
        if (!is_array($circuito) && ($circuito->ESTADO == "ABIERTO" || $circuito->ESTADO == "APERTURA")) {

            $msg .= "\n🔷 Para el inmueble consultado encontre que se reportó la siguiente falla en el servicio de energía: \n🔷 Hay una falla en el circuito reportada el " . $circuito->FECHA . " a las " . $circuito->HORA . ". Estamos trabajando para reestablecer el servicio lo más pronto posible.";
            return $msg;
        } else {
            //Invocar metodo para buscar interr SGO
            //return $this->getSGO($niu);
            return "En este momento no me reporta ninguna falla del servicio en tu sector, por favor comunicate con nosotros: ";
        }
    }

    //Método que obtiene las interrupciones a nivel de nodo SGO teniendo el NIU. Reutilizable
    public function getSGO($niu)
    {
        $res = $this->consultarIndisponibilidad($niu);
        if(substr($res->NombreSuscriptor,0,5)!="ERROR"){
            $time = explode(" ", $res->Fecha);
            //Validar si se encuentra una indisponibilidad en el SGO
            if($res->Estado==0){
                $msg = "\n🔷 Para el inmueble consultado encontré que se reportó la siguiente falla en el servicio de energía: \n🔷 Hay una falla en el nodo reportada el " . $time[0] . " a las " . $time[1] . ".";
                //Validar si ya hay cuadrillas en campo
                if($res->Orden == 1){
                    $msg .= "\n Ya tenemos una de nuetras cuadrillas en camino para solucionar este inconveniente.";
                }
                return $msg;
            }else{
                return "En este momento no me reporta ninguna falla del servicio en tu sector, por favor comunicate con nosotros: ";            
            }
        }else{
            return "No he podido encontrar ningún registro asociado con esta cuenta.";
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

    //----------------------------CONEXION SGO------------------------------------

    public function consultarIndisponibilidad($niu){
        
        $wsdl = $this->SGOurl;
        $client = new \Mesmotronic\Soap\WsaSoapClient($wsdl);
        $result = $client->ConsultarIndisponibilidad(array('Cuenta' => $niu));
        
        return $result->ConsultarIndisponibilidadResult;
    }
    
    public function consularInterrupcionesDelServicioXCuenta($niu){
    
        $wsdl = $this->SGOurl;
        $client2 = new \Mesmotronic\Soap\WsaSoapClient($wsdl);
        $result2 = $client2->consularInterrupcionesDelServicioXCuenta(array('Cuenta' => $niu));
        
        return $result2->consularInterrupcionesDelServicioXCuentaResult;
    }
    
    public function consultarIndisponibilidadXNodo($nodo){
    
        $wsdl = $this->SGOurl;
        $client3 = new \Mesmotronic\Soap\WsaSoapClient($wsdl);
        $result3 = $client3->consultarIndisponibilidadXNodo(array('Nodo' => $nodo));
        
        return $result3->consultarIndisponibilidadXNodoResult;
    }


    //----------------------------CALIFICACION DEL SERVICIO------------------------------------

    public function setCalificacion($calificacion){

        insertCalificacion($this->con, $calificacion);

        if($calificacion == '😍 Excelente'||$calificacion == '😁 Bueno'){
            $event = 'calif_positiva';
        }else{
            $event = 'calif_negativa';
        }


        $json['followupEvent'] = array(
            'name' => $event
        );
        return $json;
    }

}
