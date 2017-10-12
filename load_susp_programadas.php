<?php

require('./php-excel-reader.php'); 
require('./download_attachments.php');
//get_attachments();
/* $excel = new PhpExcelReader;
//TODO
$filename = '668925.xls';

insertDataFromExcel($excel, $filename);

function insertDataFromExcel($excel, $filename){
    $excel->read(getcwd().'\attachment\\'.$filename);
    var_dump($excel->sheets);
} */
chdir('attachment');
$data = new Spreadsheet_Excel_Reader("668400.xls", false);
var_dump($data);