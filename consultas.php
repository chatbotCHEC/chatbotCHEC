<?php

    function getData($NIU, $mng){
        $response = array();
        $filter = [ 'NIU' => floatval($NIU) ];
        $query = new MongoDB\Driver\Query($filter);
        $result = $mng->executeQuery("chatbot_db.usuarios", $query);
        $cliente = current($result->toArray());
        if (!empty($cliente)) {
            return $cliente;
        } else {
            return null;
        }
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

    function getNIUwithName($con, $palabras){

        $string = "SELECT distinct(NIU) FROM dw.DATOSBASICOS WHERE NOMBRE LIKE ";
        $last = count($palabras) - 1;
        foreach ($palabras as $i => $palabra) {
            if($i==0 && $i==$last){
                $string = $string."'".$palabra."%'";
                break;
            }
            if($i==0 && $i!=$last){
                $string = $string."'".$palabra."%' AND NOMBRE LIKE ";
                continue;
            }
            if($i==$last){
                $string = $string."'%".$palabra."%'";
                break;
            }
            $string = $string."'%".$palabra."%' AND NOMBRE LIKE ";
        }
        $datos = $con -> query($string);
        $db_response = array();
        foreach($datos as $row )
        {
             $db_response=$row;
        }
        return $db_response;
    }

    function getNIUwithTel($con, $telefono){
        $datos = $con -> query("SELECT NIU FROM dw.DATOSBASICOS WHERE TELEFONO = ".$telefono);
        $db_response = array();
        foreach($datos as $row )
        {
                $db_response=$row;
        }
        return $db_response;
    }


    function getNIUwithAddress($con, $direccion){
        $string = "SELECT NIU FROM dw.DATOSBASICOS WHERE DES_DIRECCION LIKE ";
        $last = count($direccion) - 1;
        foreach ($direccion as $i => $palabra) {
            if($i==0 && $i==$last){
                $string = $string."'".$palabra."%'";
                break;
            }
            if($i==0 && $i!=$last){
                $string = $string."'".$palabra."%' AND DES_DIRECCION LIKE ";
                continue;
            }
            if($i==$last){
                $string = $string."'% ".$palabra."'";
                break;
            }
            $string = $string."'% ".$palabra." %' AND DES_DIRECCION LIKE ";
        }
        
        $datos = $con -> query($string);
        $db_response = array();
        foreach($datos as $row )
        {
             $db_response=$row;
        }
        return $db_response;
    }

    function getNIUwithCedula($con, $cedula){
        $datos = $con -> query("SELECT NIU FROM dw.DATOSBASICOS WHERE NUM_IDENTIFICACION = ".$cedula);
        $db_response = array();
        foreach($datos as $row )
        {
                $db_response=$row;
        }
        return $db_response;
    }


?>