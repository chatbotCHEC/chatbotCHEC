<?php
    date_default_timezone_set('America/Bogota');
    function getData($NIU, $con){
        $filter = [ 'NIU' => $NIU ];
        $query = new MongoDB\Driver\Query($filter);
        $result = $con->executeQuery("chatbot_db.usuarios", $query);
        $cliente = current($result->toArray());
        return $cliente;
    }

    function getNIUwithCedula($con, $cedula){
        $filter = [ 'DOCUMENTO' => $cedula, 'TIPO_DOC' => "CC" ];
        $query = new MongoDB\Driver\Query($filter);
        $result = $con->executeQuery("chatbot_db.usuarios", $query);
        $cliente = $result->toArray();
        return $cliente;
    }

    function getNIUwithNIT($con, $nit){
        $filter = [ 'DOCUMENTO' => $nit, 'TIPO_DOC' => "NI" ];
        $query = new MongoDB\Driver\Query($filter);
        $result = $con->executeQuery("chatbot_db.usuarios", $query);
        $cliente = $result->toArray();
        return $cliente;
    }

    function getNIUwithName($con, $palabras){
        $filter = getNamesQuery($palabras);
        $query = new MongoDB\Driver\Query($filter);
        $result = $con->executeQuery("chatbot_db.usuarios", $query);
        $clientes = $result->toArray();
        return $clientes;
    }

    function getNIUwithAddress($con, $direccion){
        $filter = getAdressQuery($direccion);
        $query = new MongoDB\Driver\Query($filter);
        $result = $con->executeQuery("chatbot_db.usuarios", $query);
        $clientes = $result->toArray();
        return $clientes;
    }
    
    function getSuspProgramada($con, $niu){
        $filter = [ 'NIU' => $niu, 'ESTADO' => "ABIERTO" ];
        $query = new MongoDB\Driver\Query($filter);
        $result = $con->executeQuery("chatbot_db.susp_programadas", $query);
        $cliente = $result->toArray();
        return $cliente;
    }
    function getSuspCircuito($con, $niu){
        //Buscar el circuito correspondiente al usuario
        $filter = ['NIU' => $niu];  
        $query = new MongoDB\Driver\Query($filter);
        $result = $con->executeQuery("chatbot_db.usuarios", $query);
        $cliente = $result->toArray();
        $circuito = $cliente[0]->CIRCUITO;
        
        //Buscar las indisponibilidades del circuito del usuario
        $filter = ['CIRCUITO'=> $circuito ];
        $query = new MongoDB\Driver\Query($filter);
        $result = $con->executeQuery("chatbot_db.indisp_circuito", $query);
        $registros = $result->toArray();

        $reg_reciente = null;
        $mostRecent= 0;
        $now = time();
        foreach($registros as $r){
          $curDate = strtotime($r->FECHA." ".$r->HORA);
          if ($curDate > $mostRecent && $curDate < $now) {
             $mostRecent = $curDate;
             $reg_reciente = $r;
          }
        }
    
        return $reg_reciente;
    }

    

    function getNIUwithTel($con, $telefono){
        $filter = [ 'TELEFONO' => $telefono ];
        $query = new MongoDB\Driver\Query($filter);
        $result = $con->executeQuery("chatbot_db.usuarios", $query);
        $cliente = $result->toArray();
        return $cliente;
    }


    

    function getNamesQuery($palabras){
        $num = count($palabras);
        switch ($num) {
            case 1:
                $filter = ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[0], 'i' )];
                break;
            case 2:
                $filter = [
                    'NOMBRE' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[1], 'i' )]
                    ]  
                ];
                break;
            case 3:
                $filter = [
                    'NOMBRE' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[1], 'i' )],
                        ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[2], 'i' )]
                    ]  
                ];
                break;
            case 4:
                $filter = [
                    'NOMBRE' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[1], 'i' )],
                        ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[2], 'i' )],
                        ['$or' => [
                            ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[3], 'i' )]       
                        ]]
                    ]  
                ];
                break;
            case 5:
                $filter = [
                    'NOMBRE' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[1], 'i' )],
                        ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[2], 'i' )],
                        ['$or' => [
                            ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[3], 'i' )],
                            ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[4], 'i' )]       
                        ]]
                    ]  
                ];
                break;
            case 6:
                $filter = [
                    'NOMBRE' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[1], 'i' )],
                        ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[2], 'i' )],
                        ['$or' => [
                            ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[3], 'i' )],
                            ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[4], 'i' )],
                            ['NOMBRE' => new MongoDB\BSON\Regex( $palabras[5], 'i' )]       
                        ]]
                    ]  
                ];
                break;
            default:
                $filter = null;
                break;
        }

        return $filter;
    }

    function getAdressQuery($palabras){
        $num = count($palabras);
        switch ($num) {
            case 1:
                $filter = ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[0], 'i' )];
                break;
            case 2:
                $filter = [
                    'DIRECCION' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[1], 'i' )]
                    ]  
                ];
                break;
            case 3:
                $filter = [
                    'DIRECCION' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[1], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[2], 'i' )]
                    ]  
                ];
                break;
            case 4:
                $filter = [
                    'DIRECCION' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[1], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[2], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[3], 'i' )]
                    ]  
                ];
                break;
            case 5:
                $filter = [
                    'DIRECCION' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[1], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[2], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[3], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[4], 'i' )]
                    ]  
                ];
                break;
            case 6:
                $filter = [
                    'DIRECCION' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[1], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[2], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[3], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[4], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[5], 'i' )]
                    ]  
                ];
                break;
            case 7:
                $filter = [
                    'DIRECCION' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[1], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[2], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[3], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[4], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[5], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[6], 'i' )]
                    ]  
                ];
                break;
            case 8:
                $filter = [
                    'DIRECCION' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[1], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[2], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[3], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[4], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[5], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[6], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[7], 'i' )]
                    ]  
                ];
                break;
            case 9:
                $filter = [
                    'DIRECCION' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[1], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[2], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[3], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[4], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[5], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[6], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[7], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[8], 'i' )]
                    ]  
                ];
                break;
            case 10:
                $filter = [
                    'DIRECCION' => new MongoDB\BSON\Regex( $palabras[0], 'i' ),
                    '$and' => [
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[1], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[2], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[3], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[4], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[5], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[6], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[7], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[8], 'i' )],
                        ['DIRECCION' => new MongoDB\BSON\Regex( $palabras[9], 'i' )]
                    ]  
                ];
                break;
            
            default:
                $filter = null;
                break;
        }

        return $filter;
    }

    function insertIndispCircuito($con, $data){
        $bulk = new MongoDB\Driver\BulkWrite;  
        $a = $bulk->insert(['FECHA'=>$data['FECHA'], 'HORA'=>$data['HORA'], 'ESTADO'=>$data['ESTADO'], 'CIRCUITO'=>$data['CIRCUITO']]);      
        $result = $con->executeBulkWrite('chatbot_db.indisp_circuito', $bulk);
        return $result;
    }

?>