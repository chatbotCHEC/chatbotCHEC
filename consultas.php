<?php
// al cambiar el nombre de la DB modificarlo tambien en la funcion insertIndispCircuito
$dbname = "heroku_69tb2th4";
//$dbname="chatbot_db";

date_default_timezone_set('America/Bogota');
function getData($NIU, $con)
{
    $filter = ['NIU' => $NIU];
    $query = new MongoDB\Driver\Query($filter);
    $result = $con->executeQuery($GLOBALS['dbname'] . ".usuarios", $query);
    $cliente = current($result->toArray());
    return $cliente;
}

function getNIUwithCedula($con, $cedula)
{
    $filter = ['DOCUMENTO' => $cedula, 'TIPO_DOC' => "CC"];
    $query = new MongoDB\Driver\Query($filter);
    $result = $con->executeQuery($GLOBALS['dbname'] . ".usuarios", $query);
    $cliente = $result->toArray();
    return $cliente;
}

function getNIUwithNIT($con, $nit)
{
    $filter = ['DOCUMENTO' => $nit, 'TIPO_DOC' => "NI"];
    $query = new MongoDB\Driver\Query($filter);
    $result = $con->executeQuery($GLOBALS['dbname'] . ".usuarios", $query);
    $cliente = $result->toArray();
    return $cliente;
}

function getNIUwithName($con, $palabras)
{
    $filter = getNamesQuery($palabras);
    $query = new MongoDB\Driver\Query($filter);
    $result = $con->executeQuery($GLOBALS['dbname'] . ".usuarios", $query);
    $clientes = $result->toArray();
    return $clientes;
}

function getNIUwithAddress($con, $direccion)
{
    $filter = getAdressQuery($direccion);
    $options = [
        'limit' => 5,
    ];
    $query = new MongoDB\Driver\Query($filter, $options);
    $result = $con->executeQuery($GLOBALS['dbname'] . ".usuarios", $query);
    $clientes = $result->toArray();
    return $clientes;
}

function getSuspProgramada($con, $niu)
{
    $filter = ['NIU' => $niu, 'ESTADO' => "ABIERTO"];
    $query = new MongoDB\Driver\Query($filter);
    $result = $con->executeQuery($GLOBALS['dbname'] . ".susp_programadas", $query);
    $cliente = $result->toArray();
    $futuras = array();
    
    $now = time();
    foreach ($cliente as $key => $value) {
       
       // var_dump($now);

        $date = $value->FECHA_FIN.' '.$value->HORA_FIN;
        //var_dump($date);
        
        $format = "d/m/Y H:i";
        $dateobj = DateTime::createFromFormat($format, $date);
        $iso_datetime = $dateobj->format(Datetime::ATOM);
        $fecha_def = strtotime($iso_datetime);
       // var_dump($fecha_def);
        if($fecha_def>$now){
            
            array_push($futuras, $value);
        }
        //var_dump($fecha_def>$now);
    }
   // var_dump($futuras);
    
    return $futuras;

}
function getSuspCircuito($con, $niu)
{
    //Buscar el circuito correspondiente al usuario
    $filter = ['NIU' => $niu];
    $query = new MongoDB\Driver\Query($filter);
    $result = $con->executeQuery($GLOBALS['dbname'] . ".usuarios", $query);
    $cliente = $result->toArray();
    $reg_reciente = array();
    if (count($cliente) > 0) {
        $circuito = $cliente[0]->CIRCUITO;

        //Buscar las indisponibilidades del circuito del usuario
        $filter = ['CIRCUITO' => $circuito];
        $query = new MongoDB\Driver\Query($filter);
        $result = $con->executeQuery($GLOBALS['dbname'] . ".indisp_circuito", $query);
        $registros = $result->toArray();

        $mostRecent = 0;
        $now = time();
        foreach ($registros as $r) {
            $curDate = strtotime($r->FECHA . " " . $r->HORA);
            if ($curDate > $mostRecent && $curDate < $now) {
                $mostRecent = $curDate;
                $reg_reciente = $r;
            }
        }

    }

    return $reg_reciente;
}


function getSuspEfectiva($con, $niu)
{
     
    //Buscar Suspenciones efectivas
    $id = new \MongoDB\BSON\ObjectId();
    $filter = ['NIU' => $niu];
    $query = new MongoDB\Driver\Query($filter);
    $result = $con->executeQuery($GLOBALS['dbname'] . ".susp_efectivas", $query);
    //var_dump($result);
    
    $cliente = $result->toArray();
    //var_dump($cliente);

    $reg_reciente = array();
    
    if (count($cliente) > 0) {
        $efectiva = $cliente[0]->NIU;
        

        $mostRecent = 0;
        $now = Time();
        //var_dump($now);

            foreach($cliente as $r ){
                $curDate = strtotime($r->HORA_FIN);                
                
                if($curDate > $mostRecent){
                    $mostRecent = $curDate;
                    $reg_reciente = $r;
                }

            }

            
        }
        //var_dump($reg_reciente);

        return $reg_reciente;

    
    
}


    





function getNIUwithTel($con, $telefono)
{
    $filter = ['TELEFONO' => $telefono];
    $query = new MongoDB\Driver\Query($filter);
    $result = $con->executeQuery($GLOBALS['dbname'] . ".usuarios", $query);
    $cliente = $result->toArray();
    return $cliente;
}

function getNamesQuery($palabras)
{
    $num = count($palabras);
    switch ($num) {
        case 1:
            $filter = ['NOMBRE' => new MongoDB\BSON\Regex($palabras[0], 'i')];
            break;
        case 2:
            $filter = [
                'NOMBRE' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['NOMBRE' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                ],
            ];
            break;
        case 3:
            $filter = [
                'NOMBRE' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['NOMBRE' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                    ['NOMBRE' => new MongoDB\BSON\Regex($palabras[2], 'i')],
                ],
            ];
            break;
        case 4:
            $filter = [
                'NOMBRE' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['NOMBRE' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                    ['NOMBRE' => new MongoDB\BSON\Regex($palabras[2], 'i')],
                    ['$or' => [
                        ['NOMBRE' => new MongoDB\BSON\Regex($palabras[3], 'i')],
                    ]],
                ],
            ];
            break;
        case 5:
            $filter = [
                'NOMBRE' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['NOMBRE' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                    ['NOMBRE' => new MongoDB\BSON\Regex($palabras[2], 'i')],
                    ['$or' => [
                        ['NOMBRE' => new MongoDB\BSON\Regex($palabras[3], 'i')],
                        ['NOMBRE' => new MongoDB\BSON\Regex($palabras[4], 'i')],
                    ]],
                ],
            ];
            break;
        case 6:
            $filter = [
                'NOMBRE' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['NOMBRE' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                    ['NOMBRE' => new MongoDB\BSON\Regex($palabras[2], 'i')],
                    ['$or' => [
                        ['NOMBRE' => new MongoDB\BSON\Regex($palabras[3], 'i')],
                        ['NOMBRE' => new MongoDB\BSON\Regex($palabras[4], 'i')],
                        ['NOMBRE' => new MongoDB\BSON\Regex($palabras[5], 'i')],
                    ]],
                ],
            ];
            break;
        default:
            $filter = null;
            break;
    }
    return $filter;
}

function getAdressQuery($palabras)
{
    $num = count($palabras);
    switch ($num) {
        case 1:
            $filter = ['DIRECCION' => new MongoDB\BSON\Regex($palabras[0], 'i')];
            break;
        case 2:
            $filter = [
                'DIRECCION' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                ],
            ];
            break;
        case 3:
            $filter = [
                'DIRECCION' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[2], 'i')],
                ],
            ];
            break;
        case 4:
            $filter = [
                'DIRECCION' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[2], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[3], 'i')],
                ],
            ];
            break;
        case 5:
            $filter = [
                'DIRECCION' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[2], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[3], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[4], 'i')],
                ],
            ];
            break;
        case 6:
            $filter = [
                'DIRECCION' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[2], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[3], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[4], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[5], 'i')],
                ],
            ];
            break;
        case 7:
            $filter = [
                'DIRECCION' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[2], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[3], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[4], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[5], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[6], 'i')],
                ],
            ];
            break;
        case 8:
            $filter = [
                'DIRECCION' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[2], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[3], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[4], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[5], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[6], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[7], 'i')],
                ],
            ];
            break;
        case 9:
            $filter = [
                'DIRECCION' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[2], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[3], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[4], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[5], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[6], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[7], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[8], 'i')],
                ],
            ];
            break;
        case 10:
            $filter = [
                'DIRECCION' => new MongoDB\BSON\Regex($palabras[0], 'i'),
                '$and' => [
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[1], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[2], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[3], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[4], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[5], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[6], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[7], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[8], 'i')],
                    ['DIRECCION' => new MongoDB\BSON\Regex($palabras[9], 'i')],
                ],
            ];
            break;

        default:
            $filter = null;
            break;
    }

    return $filter;
}



function insertSuspensionesEfectivas($con, $data)
{
    $bulk = new MongoDB\Driver\BulkWrite;
    $a = $bulk->insert(['ID_ORDEN' => $data['0'], 'NIU' => $data['1'], 'FECHA_ATENCION' => $data['2'], 
    'HORA_INI' => $data['3'], 'HORA_FIN' => $data['4'], 'DESCRIPCION' => $data['5'], 'VALOR' => $data['6']]);
    $result = $con->executeBulkWrite($GLOBALS['dbname'] . '.susp_efectivas', $bulk);
    return $result;
}

function insertIndispCircuito($con, $data)
{
    $dbname = 'heroku_69tb2th4';
    $bulk = new MongoDB\Driver\BulkWrite;
    $a = $bulk->insert(['FECHA' => $data['FECHA'], 'HORA' => $data['HORA'], 'ESTADO' => $data['ESTADO'], 'CIRCUITO' => $data['CIRCUITO']]);
    $result = $con->executeBulkWrite($dbname . '.indisp_circuito', $bulk);
    return $result;
}

function insertSuspProgramada($con, $data)
{
    $bulk = new MongoDB\Driver\BulkWrite;
    $a = $bulk->insert(['COD_TRAFO' => $data['COD_TRAFO'], 'NIU' => $data['NIU'], 'FECHA_INICIO' => $data['FECHA_INICIO'], 'FECHA_FIN' => $data['FECHA_FIN'], 'HORA_INICIO' => $data['HORA_INICIO'], 'HORA_FIN' => $data['HORA_FIN'], 'ESTADO' => $data['ESTADO'], 'ORDEN_OP' => $data['ORDEN_OP']]);
    $result = $con->executeBulkWrite($GLOBALS['dbname'] . '.susp_programadas', $bulk);
    return $result;
}

function updSuspProgramada($con, $orden_op)
{
    $bulk = new MongoDB\Driver\BulkWrite;
    $a = $bulk->update(
        ['ORDEN_OP' => $orden_op],
        ['$set' => ['ESTADO' => 'CANCELADO']],
        ['multi' => true, 'upsert' => false]
    );
    $result = $con->executeBulkWrite($GLOBALS['dbname'] . '.susp_programadas', $bulk);
    return $result;
}
