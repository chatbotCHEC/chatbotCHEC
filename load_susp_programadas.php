<?php

require('./excel_reader.php'); 
require('./download_attachments.php');
$excel = new PhpExcelReader;
//TODO
$filename = '668168.xls';

insertDataFromExcel($excel, $filename);

function insertDataFromExcel($excel, $filename){
    $excel->read('./attachment/'.$filename);
    var_dump($excel->sheets);
}