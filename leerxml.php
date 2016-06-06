<?php

$folder = "/var/www/cron/xml";
$directorio = opendir($folder); //ruta actual
function original_ticket_search($ticket){//Función para buscar el ticket original en caso de cambio de status
			$ticketOriginal=$ticket->KIU_OriginalTicketNumber;
			$ticketOriginal=$ticket->KIU_OriginalTicketInfo["KIU_OriginalTicketNumber"];
            return $ticketOriginal;
		}

while ($archivo = readdir($directorio)) //Obtenemos un archivo y luego otro sucesivamente
{
	$cont++;
	$xml_file = $folder."/".$archivo;
	$xml = simplexml_load_file($xml_file);//Convertir en Objeto.
	$TimestampGMT = $xml["TimestampGMT"];//Fecha de emisión
	
	foreach ($xml->KIU_TktDisplay as $ticket) {//FOREACH Recorrer todos los datos del boleto
		echo "<h3>$cont ".$ticket->TicketItemInfo["TicketNumber"]." Fecha: ".$TimestampGMT;
		//$ticketOriginal=$ticket->KIU_OriginalTicketInfo["KIU_OriginalTicketNumber"]; //ticket original reemitido jmangarret may2016
		$ticketOriginal=original_ticket_search($ticket);
		if ($ticketOriginal>0){
			$sqlUpdate="UPDATE boletos SET status_emission='Reemitido' WHERE ticketNumber='".$ticketOriginal."'";
			echo "ticketOriginal: ".$ticketOriginal;
		}else{
			echo "Ticket original no encontrado";
		}
	}
	
}
?>
