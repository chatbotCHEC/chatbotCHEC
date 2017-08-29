<?php

    function getData($con, $NIU){
        $datos = $con -> query("SELECT NIU, NOMBRE, DES_DIRECCION, TELEFONO, COD_TRAFO FROM chec.usuarios where NIU = '".$NIU."';");
        $db_response = array();
        foreach($datos as $row )
        {
             $db_response=$row;
        }
        return $db_response;
    }

    function getDisponibilidad($con, $COD_TRAFO){
        $circuito_res = $con -> query("SELECT COD_CIRCUITO FROM chec.trafo WHERE COD_TRAFO ='".$COD_TRAFO."'");
        $COD_CIRCUITO = array();
        foreach($circuito_res as $row){
            $COD_CIRCUITO = $row;
        }
        if(!(isset($COD_CIRCUITO['COD_CIRCUITO'])) || $COD_CIRCUITO['COD_CIRCUITO']== ""){
            return null;
        }else{
            $datos = $con -> query("SELECT CIRCUITO, FECHA, HORA, CONCAT(FECHA,' ',HORA) as TS, ESTADO FROM chec.indisponibilidadestel
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

        $string = "SELECT NIU FROM chec.usuarios WHERE NOMBRE LIKE ";
        $last = count($palabras) - 1;
        foreach ($palabras as $i => $palabra) {
            if($last!=0){
                if($i==$last){
                    $string = $string."'%".$palabra."%'";
                    break;
                }
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
                $datos = $con -> query("SELECT NIU FROM chec.usuarios WHERE TELEFONO = ".$telefono);
                $db_response = array();
                foreach($datos as $row )
                {
                     $db_response=$row;
                }
                return $db_response;
    }

    function getDiasMora($con, $niu){
        $datos = $con -> query("SELECT DIAS_MORA, VR_MORA FROM chec.facturacion WHERE NIU ='".$niu."'");
        $db_response = array();
        foreach($datos as $row )
        {
             $db_response=$row;
        }
        return $db_response;
    }


?>