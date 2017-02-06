<?php
/**
* Clase para las sentencias SQL de los Boletos KIU
*/include "items.php";
class registro extends items{

	public static function conectar_db(){
		global $servidor, $usuario, $pass;
		//DESARROLLO
		//$conectar = mysql_connect('humbermarccs.dyndns.tv', 'adminroot', 'adminr00t24');
		//PRODUCCION
		$conectar = mysql_connect($servidor, $usuario, $pass);

		//$conectar=mysql_connect("humbermarccs.dyndns.tv","adminroot","adminr00t24");
		mysql_select_db('registro_boletos',$conectar);
		/*Verificar conexión*/
		if (!$conectar) {
		    echo "/*--------------------------------------------*/<br>";
			printf("Error al conectar: %s\n", mysql_errno().'<br>');
		  	echo "/*--------------------------------------------*/<br>";
			exit();
		}
		//return $conectar;
	}

	public static function consultar_registro($tabla){
		self::conectar_db();
		$registos = array();
		$consulta = "SELECT * FROM $tabla ORDER BY fecha DESC";//Consultar fecha del último archivo que se registró
		if($variable = mysql_query($consulta))
		{
		    if (mysql_num_rows($variable) > 0) //si la variable tiene al menos 1 fila entonces seguimos con el codigo
		    {
		        while ($row = mysql_fetch_array($variable)) 
		        {
		            $registos[] = $row;
		        }
		    }
		}
		return $registos;
	}

	public static function validar_registro($arreglo,$archivo){
		foreach ($arreglo as $nombre) {
			if ($nombre["nombre"] == $archivo)
			{
				$registrado = TRUE;
				break;
			}else{
				$registrado = FALSE;
			}
		}
		return $registrado;
	}

	public static function insertar($archivo,$fecha,$tabla){
		$query = "INSERT INTO $tabla (nombre, fecha, status)
                    VALUES ('".$archivo."','".$fecha."','procesado')";
        return $query;
	}

	public static function consultar_ultimo_id($archivo,$fecha,$tabla){
		$consultar_id = "SELECT id FROM $tabla WHERE nombre = '".$archivo."' AND fecha ='".$fecha."'";
		return $consultar_id;
	}

public static function recorrer_xml($ticket,$id,$TimestampGMT){
		$items = new items();
		// Recorremos el XML

            $yn_tax = $items->tax_yn_search($ticket);//Buscar el impuesto YN del boleto
                        
            $name_satel = $items->name_agent_search($ticket);//Buscar los nombres de los agentes por terminal

            $name_agent = $items->name_satel_search($ticket);//Buscar los nombres de los satélites por terminal
                        
            $itinerary = $items->itinerary_search($ticket);//Buscar el itinerario de vuelo (Si es solo ida o ida y vuelta)

            $iata_transac = $items->status_emission_name_search($ticket);//Buscar el nombre del status de emisión del boleto KIU

            $method = $items->cod_method_payment_search($ticket);//Buscar el nombre del método de pago del boleto

            $coupon_status = $items->coupon_status_search($ticket);//Buscar el nombre del estatus del cupón

            $TicketNumber = $items->process_ticketNumber($ticket);//Anexar guión en el número del ticket

            $tipo_vuelo = 	$items->process_tipo_vuelo($ticket->TicketItemInfo["StatisticalCode"]);//Traduce tipo de vuela

            $localizador = 	$items->process_localizador($ticket);//Anexar status al id del localizador
			            
            $passenger 	= $ticket->PassengerName->Surname."/".$ticket->PassengerName->GivenName;
                       //Restamos 3 horas y media por diferencia horaria de servidores Kiu.
			$nuevafecha = strtotime ('-3 hour',strtotime($TimestampGMT));
			$nuevafecha = date ( 'Y-m-d H:i:s' , $nuevafecha );
			$nuevafecha = strtotime ('-30 minute',strtotime($nuevafecha));
			$nuevafecha = date ( 'Y-m-d H:i:s' , $nuevafecha );

            $creationDate=$items->process_date($ticket,$nuevafecha);

            //jmangarret 06ene2017 - Modificamos aerolinea K8 para convertirla en Laser/QL
            $MarketingAirline=$ticket->FlightReference->FlightSegment["MarketingAirline"];
            $airline=($MarketingAirline=="K8" ? "QL" : $MarketingAirline);
            
            // Inserta los datos del XML en la tabla 
            $qry = "INSERT INTO boletos ".
                "(id_xml, localizador, currency, fee_percentage, fee, total_amount, montobase, coupon_status, passenger, sistemagds,
                emittedDate, creationDate, departureDate, arrivalDate, ticketNumber, airlineID, YN_tax, total_tax, status_emission,
                ID_asesora, nombre_asesora, ID_satelite, nombre_satelite, tipo_vuelo, method_payment, itinerary)".
                " VALUES ('".$id."',
                '".$localizador."',
                '".$ticket->TotalFare["CurrencyCode"]."',
                '".$ticket->Commission["Percentage"]."',
                '".$ticket->Commission["Amount"]."',
                '".$ticket->TotalFare["Amount"]."',
                '".$ticket->EquivFare["Amount"]."',
                '".$coupon_status."',
                '".$passenger."',
                'Kiu',
                '".$nuevafecha."',
                '".$creationDate."',
                '".$ticket->FlightReference->FlightSegment["DepartureDateTime"]."',
                'NULL',
                '".$TicketNumber."',
                '".$airline."',
                '".$yn_tax."',
                '".$ticket->FormsOfPayment->FormOfPayment["AmountTaxes"]."',
                '".$iata_transac."',
                '".$ticket->TicketItemInfo["IssuingAgentInfo"]."',
                '".$name_agent."',
                '".$ticket->BOD->Source["AgentSine"]."',
                '".$name_satel."',
                '".$tipo_vuelo."',
                '".$method."',
                '".$itinerary."')";
        return $qry;
	}

	public static function logs($codigo_error, $mesaje_error, $descripcion, $fecha_error){//Registro de suscesos en el CRON
		$querry_log = "INSERT INTO logs (codigo_error, mesaje_error, descripcion, fecha_error) VALUES ('".$codigo_error."','".$mesaje_error."','".$descripcion."','".$fecha_error."')";
		return($querry_log);
	}
}
?>