<?php


//ESTE BLOQUE TRABAJA CON SUSPENSIONES PROGRAMADAS
function get_attachments(){
    set_time_limit(3000); 

    /* connect to gmail with your credentials */
    $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
    $username = 'prjchec.suspensiones_programadas@umanizales.edu.co'; 
    $password = 'umCHEC1234_761349';
    
    /* try to connect */
    $inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
    
    $emails = imap_search($inbox, 'FROM "prjchec.dcardona@umanizales.edu.co" UNSEEN');
    
    /* if any emails found, iterate through each email */
    if($emails) {
    
        $count = 1;
    
        /* put the newest emails on top */
        rsort($emails);
    
        /* for every email... */
        foreach($emails as $email_number) 
        {
    
            /* get information specific to this email */
            $overview = imap_fetch_overview($inbox,$email_number,0);
    
            $message = imap_fetchbody($inbox,$email_number,2);
    
            /* get mail structure */
            $structure = imap_fetchstructure($inbox, $email_number);
    
            $attachments = array();
    
            /* if any attachments found... */
            if(isset($structure->parts) && count($structure->parts)) 
            {
                for($i = 0; $i < count($structure->parts); $i++) 
                {
                    $attachments[$i] = array(
                        'is_attachment' => false,
                        'filename' => '',
                        'name' => '',
                        'attachment' => ''
                    );
    
                    if($structure->parts[$i]->ifdparameters) 
                    {
                        foreach($structure->parts[$i]->dparameters as $object) 
                        {
                            if(strtolower($object->attribute) == 'filename') 
                            {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['filename'] = $object->value;
                            }
                        }
                    }
    
                    if($structure->parts[$i]->ifparameters) 
                    {
                        foreach($structure->parts[$i]->parameters as $object) 
                        {
                            if(strtolower($object->attribute) == 'name') 
                            {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['name'] = $object->value;
                            }
                        }
                    }
    
                    if($attachments[$i]['is_attachment']) 
                    {
                        $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);
    
                        /* 3 = BASE64 encoding */
                        if($structure->parts[$i]->encoding == 3) 
                        { 
                            $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                        }
                        /* 4 = QUOTED-PRINTABLE encoding */
                        elseif($structure->parts[$i]->encoding == 4) 
                        { 
                            $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                        }
                    }
                }
            }
    
      
    
            /* iterate through each attachment and save it */
            foreach($attachments as $attachment)
            {
                if($attachment['is_attachment'] == 1)
                {
                    $filename = substr($attachment['name'], 0, -3)."html";
                    if(empty($filename)) $filename = $attachment['filename'];
    
                    if(empty($filename)) $filename = time() . ".dat";
                    $folder = "attachment";
                    if(!is_dir($folder))
                    {
                         mkdir($folder);
                    }
                    if(file_exists("./". $folder ."/". $filename)){
                        $filename = 'c-'.$filename;
                    }
                    $fp = fopen("./". $folder ."/". $filename, "w+");
                    fwrite($fp, $attachment['attachment']);
                    fclose($fp);
                    chmod("./". $folder ."/". $filename, 0666);
                }
            }
        }
    } 
    
    /* close the connection */
    imap_close($inbox);
    return true;
}

// ESTE BLOQUE TRABAJA CON SCADA
function get_mail_body(){
    //set_time_limit(6000); 

    /* connect to gmail with your credentials */
    $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
    $username = 'prjchec.indisponibilidades_circuito@umanizales.edu.co'; 
    $password = 'umCHEC1234_761349';
    
    /* try to connect */
    $inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
    
    //$emails = imap_search($inbox, 'FROM "notificacionsgo@chec.com.co" SEEN');
    $emails = imap_search($inbox, 'FROM "prjchec.dcardona@umanizales.edu.co" UNSEEN');
    
    /* if any emails found, iterate through each email */
    if($emails) {
    
        $count = 1;
    
        /* put the newest emails on top */
        rsort($emails);   
        //for every email...
        foreach($emails as $email_number) 
        {
    
            /* get information specific to this email */
            $overview = imap_fetch_overview($inbox,$email_number,0);
    
            $message = imap_fetchbody($inbox,$email_number,2);
            

       
            $content = imap_headerinfo($inbox, $email_number);



            if(strpos($content->subject,"Fwd:")){
                $inicioCadena = "201";
                $pos = strpos($content->subject, $inicioCadena);
                $return = substr($content->subject, $pos);

            }else{
                $return = $content->subject;            
            }
    
        
            saveIndispCircuito($return);

           
        }
    } 
    
    /* close the connection */
    imap_close($inbox);
    return true;
}

//ESTE BLOQUE TRABAJA CON SUSPENSIONES EFECTIVAS
function get_attachments_efectivas(){
    set_time_limit(3000); 

    /* connect to gmail with your credentials */
    $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
    $username = 'prjchec.suspensiones_efectivas@umanizales.edu.co'; 
    $password = 'umCHEC1234';
    
    /* try to connect */
    $inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
    
    $emails = imap_search($inbox, 'FROM "prjchec.dcardona@umanizales.edu.co" UNSEEN');
    
    /* if any emails found, iterate through each email */
    if($emails) {
    
        $count = 1;
    
        /* put the newest emails on top */
        rsort($emails);
    
        /* for every email... */
        foreach($emails as $email_number) 
        {
    
            /* get information specific to this email */
            $overview = imap_fetch_overview($inbox,$email_number,0);
    
            $message = imap_fetchbody($inbox,$email_number,2);
    
            /* get mail structure */
            $structure = imap_fetchstructure($inbox, $email_number);
    
            $attachments = array();
    
            /* if any attachments found... */
            if(isset($structure->parts) && count($structure->parts)) 
            {
                for($i = 0; $i < count($structure->parts); $i++) 
                {
                    $attachments[$i] = array(
                        'is_attachment' => false,
                        'filename' => '',
                        'name' => '',
                        'attachment' => ''
                    );
    
                    if($structure->parts[$i]->ifdparameters) 
                    {
                        foreach($structure->parts[$i]->dparameters as $object) 
                        {
                            if(strtolower($object->attribute) == 'filename') 
                            {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['filename'] = $object->value;
                            }
                        }
                    }
    
                    if($structure->parts[$i]->ifparameters) 
                    {
                        foreach($structure->parts[$i]->parameters as $object) 
                        {
                            if(strtolower($object->attribute) == 'name') 
                            {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['name'] = $object->value;
                            }
                        }
                    }
    
                    if($attachments[$i]['is_attachment']) 
                    {
                        $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);
    
                        /* 3 = BASE64 encoding */
                        if($structure->parts[$i]->encoding == 3) 
                        { 
                            $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                        }
                        /* 4 = QUOTED-PRINTABLE encoding */
                        elseif($structure->parts[$i]->encoding == 4) 
                        { 
                            $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                        }
                    }
                }
            }
    
      
    
            /* iterate through each attachment and save it */
            foreach($attachments as $attachment)
            {
                if($attachment['is_attachment'] == 1)
                {
                   if(empty($filename)) $filename = $attachment['filename'];
    
                    if(empty($filename)) $filename = time() . ".dat";
                    //FOLDER DE SUSPENSIONES EFECTIVAS
                    $folder = "attachment_efectivas";
                    if(!is_dir($folder))
                    {
                         mkdir($folder);
                    }
                    if(file_exists("./". $folder ."/". $filename)){
                        $filename = 'c-'.$filename;
                    }
                    $fp = fopen("./". $folder ."/". $filename, "w+");
                    fwrite($fp, $attachment['attachment']);
                    fclose($fp);
                    chmod("./". $folder ."/". $filename, 0666);
                }
            }
        }
    } 
    
    /* close the connection */
    imap_close($inbox);
    return true;


}


function saveIndispCircuito($global){
	require('./lib.php');
	//Instancia de la API
	$api = new chatBotApi();
	
	$fecha = "";
	$time = "";
	$condition = "";
	$cod_circuit = "";
	$suscribers = "";
	
	
	$data = $api->getIndisponibilidadCircuitoData($global);
	var_dump($data);
	
	$response = $api->setIndispCircuito($data);
	
	echo "papi donde esta el funk?";
}


?>