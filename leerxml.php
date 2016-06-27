<?php
include "config.php";//Incluir la clase registro
$folder = "/var/www/cron_kiu/xml";
$directorio = opendir($folder); //ruta actual
$drive_folder=$folder;

$m=date("F");
$y=date("Y");
/*ZIP PROCESADOS*/
$xml_folder_processed = $xml_folder_processed."/".$y;
if (!file_exists($xml_folder_processed)) {
	mkdir($xml_folder_processed, 0777, true);
}
$xml_folder_processed = $xml_folder_processed."/".$m;
if (!file_exists($xml_folder_processed)) {
	mkdir($xml_folder_processed, 0777, true);
}
/*XML PROCESADOS*/
$drive_folder_processed = $drive_folder_processed."/".$y;
if (!file_exists($drive_folder_processed)) {
	mkdir($drive_folder_processed, 0777, true);
}		
$drive_folder_processed = $drive_folder_processed."/".$m;
if (!file_exists($drive_folder_processed)) {
	mkdir($drive_folder_processed, 0777, true);
}		

while ($archivo = readdir($directorio)) //Obtenemos un archivo y luego otro sucesivamente
{
	$cont++;
	$xml_file = $folder."/".$archivo;
	//$xml = simplexml_load_file($xml_file);//Convertir en Objeto.
	//$TimestampGMT = $xml["TimestampGMT"];//Fecha de emisión


    rename ($drive_folder."/".$archivo,$xml_folder_processed."/".$archivo);

    echo "<br>VUELTA ".$cont . " - ".$xml_folder_processed;
	
}
?>
