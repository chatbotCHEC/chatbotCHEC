<?php
try
{
    $soap_client = new
        SoapClient("https://webservicedes.chec.com.co/WCF_Indisponibilidad/ServiceIndisponibilidad.svc?wsdl");
    $vec = array("symbol"=>"DOX");
    $quote = $soap_client->GetQuote($vec);
    echo $quote->GetQuoteResult;

    echo "********************************************";

    $niu = "182894608";
    $result = $client->ConsultarIndisponibilidad($niu);
    echo $result;
}
catch(SoapFault $exception)
{
    echo $exception->getMessage();
}

?>