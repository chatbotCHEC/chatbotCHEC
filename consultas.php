<?php

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
        var_dump($query);
        $result = $con->executeQuery("chatbot_db.usuarios", $query);
        $clientes = $result->toArray();
        return $clientes;
    }
    
    function getDisponibilidad($con, $COD_TRAFO){
        $circuito_res = $con -> query("SELECT COD_CIRCUITO FROM dw.DIM_TRAFOS WHERE COD_TRAFO ='".$COD_TRAFO."'");
        $COD_CIRCUITO = array();
        foreach($circuito_res as $row){
            $COD_CIRCUITO = $row;
        }
        if(!(isset($COD_CIRCUITO['COD_CIRCUITO'])) || $COD_CIRCUITO['COD_CIRCUITO']== ""){
            return null;
        }else{
            $datos = $con -> query("SELECT CIRCUITO, FECHA, HORA, CONCAT(FECHA,' ',HORA) as TS, ESTADO FROM dbo.INDISP_TR
            WHERE CIRCUITO ='".$COD_CIRCUITO['COD_CIRCUITO']."' 
            ORDER BY TS
            DESC
            LIMIT 1");
            $db_response = array();
            foreach($datos as $row )
            {
                 $db_response=$row;
            }
            return $db_response;
        }
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

?>