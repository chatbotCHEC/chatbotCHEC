<?php

    function getData($con, $NIU){
        $datos = $con -> query("SELECT NIU, NOMBRE, DES_DIRECCION, TELEFONO, COD_TRAFO FROM chec.usuarios where NIU = '".$NIU."';");
        $db_response = array();
        foreach($datos as $row )
        {
             $db_response['Data']=$row;
        }
        return $db_response;
    }

    function getDisponibilidad($con, $COD_TRAFO){
        $circuito_res = $con -> query("SELECT COD_CIRCUITO FROM chec.trafo WHERE COD_TRAFO ='".$COD_TRAFO."'");
        $COD_CIRCUITO = array();
        foreach($circuito_res as $row){
            $COD_CIRCUITO = $row;
        }
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
?>