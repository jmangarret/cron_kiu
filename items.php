<?php

	class items{

		public static function terminales(){//Función de listado de terminales con sus respectivos nombres de usuario
			$terminales = [
				 "BLAJ06401" => "SALES/BEATRIZ",
				 "BLAJ06402" => "SALES/ROYAL TOUR",
				 "BLAJ06403" => "SALES/SARAY",
				 "BLAJ06404" => "SALES/ROSANNY",
				 "BLAJ06405" => "SALES/YUVY",
				 "BLAJ06406" => "SALES/ROBERTO",
				 "BLAJ06407" => "SALES/WLADIMIR",
				 "BLAJ06408" => "SALES/BESTRAVEL-MARLENE",
				 "BLAJ06409" => "SALES/EUCARIS",
				 "BLAJ0640A" => "SALES/SUN TRAVEL LIFE",
				 "BLAJ0640B" => "SALES/MUNDO TROPICAL",
				 "BLAJ0640C" => "SALES/SRA. MARIELA - CORO",
				 "BLAJ0640D" => "SALES/MEGATRAVEL-MARIA",
				 "BLAJ0640E" => "SALES/MEDANO-TOURS",
				 "BLAJ0640F" => "SALES/DIANA",
				 "BLAJ0640G" => "SALES/KAREMS TRAVEL",
				 "BLAJ0640H" => "SALES/LECHERIA-MARIA",
				 "BLAJ0640I" => "SALES/MIAGENCIA-2",
				 "BLAJ0640J" => "SALES/BEST TRAVEL",
				 "BLAJ0640K" => "SALES/MEGA-MARLENE-2",
				 "BLAJ0640L" => "SALES/SRA.MARIELA-CORO-2",
				 "BLAJ0640M" => "SALES/LIMBO TRAVELS-2",
				 "BLAJ0640N" => "SALES/BARITURISMO",
				 "BLAJ0640O" => "SALES/MIAGENCIA-3",
				 "BLAJ0640P" => "SALES/EKT TOURS MARGARITA-01",
				 "BLAJ0640Q" => "SALES/EKT TOURS MARGARITA-02",
				 "BLAJ0640R" => "SALES/TURISTOUR",
				 "BLAJ0640S" => "SALES/MEGA-MARLENE-3",
				 "BLAJ0640T" => "SALES/BEST-TRAVEL-5",
				 "BLAJ0640U" => "SALES/BESTRAVEL-6",
				 "BLAJ0640V" => "SALES/BESTRAVEL-7",
				 "BLAJ0640W" => "SALES/YENIFFER",
				 "BLAJ0640X" => "SALES/SRA. ANDREINA VASQUEZ",
				 "BLAJ0640Y" => "SALES/SRA. ANDREINA-SANDRA",
				 "BLAJ0640Z" => "SALES/KALISCAPE",
				 "BLAJ06410" => "SALES/KALISCAPE-2",
				 "BLAJ06411" => "SALES/MANUELA-RIVAS",
				 "BLAJ06412" => "SALES/TREMONT-TOURS",
				 "BLAJ06413" => "SALES/AGENT13",
				 "BLAJ064SM" => "ADMIN/ADMIN",
			];
			return $terminales;
		}

		public static function cod_iata_transac(){//Función de listado de Status del Boleto según KIU
			$cod_iata_transac = [
				"TKTT" => "Emitido",
				"CANX" => "Anulado",
				"EMDS" => "Electronic Miscellaneous Document",
			];
			return $cod_iata_transac;
		}

		public static function cod_method_payment(){//Funión de listado de metodos de pago de boletos KIU
			$cod_method_payment = [
				"CA" => "Efectivo",
				"CC" => "Credito",
				"DC" => "Debito",
				"CK" => "Cheque",
				"IN" => "Invoice",
				"MS" => "Mixto",
			];
			return $cod_method_payment;
		}

		public static function cod_coupon_status(){//Función de listado de estatus de cupones
			$cod_coupon_status = [
				"O" => "Emitido",
				"V" => "Anulado",
				"E" => "Reemitido",
			];
			return $cod_coupon_status;
		}

		public static function name_agent_search($ticket){//Funcíon para buscar el nombre del agente
			foreach ($ticket->BOD->Source as $terminal_agent) {
                foreach (self::terminales() as $key_agent => $value_agent) {
                    if ($terminal_agent["AgentSine"] == $key_agent) {
                        $name_agent = explode("/", $value_agent);
                        $name_agent = end($name_agent);
                    }
                }
            }
            return $name_agent;
		}

		public static function name_satel_search($ticket){//Función para buscar el nombre del satélite
			foreach ($ticket->TicketItemInfo as $terminal_satelite) {
                foreach (self::terminales() as $key_satel => $value_satel) {
                    if ($terminal_satelite["IssuingAgentInfo"] == $key_satel) {
                        $name_satel = explode("/", $value_satel);
                        $name_satel = end($name_satel);
                    }
                }
            }
            return $name_satel;
		}

		public static function original_ticket_search($ticket){//Función para buscar el ticket original en caso de cambio de status
			$ticketOriginal="";			
			$ticketOriginal=$ticket->KIU_OriginalTicketInfo["KIU_OriginalTicketNumber"];
            return $ticketOriginal;
		}

		public static function status_emission_name_search($ticket){//Función buscar el nombre del status de emisión del boleto KIU
                $status_name="";
                foreach (self::cod_iata_transac() as $key_satus => $value_status) {
                    if ($ticket["IATA_Transac"] == $key_satus) {
                        $status_name = $value_status;
                    }
                }
                //Validamos si es una reemision jmangarret may2016                
	            if (self::original_ticket_search($ticket)>0){
	                $status_name="Reemitido";
	            }
            return $status_name;
		}

		public static function tax_yn_search($ticket){//Función buscar el impesto YN del boleto
			$yn_tax = "NULL";
			foreach ($ticket->Taxes->Tax as $key => $tax) {
                if ($tax["TaxCode"] == "YN") {
                    $yn_tax = $tax["Amount"];
                }
            }
            return $yn_tax;
		}

		public static function itinerary_search($ticket){//Función para el itinerario de vuelo (Si es solo ida o ida y vuelta)
			$cont = 0;
            foreach ($ticket->FlightReference as $itinerary) {
                $cont++;
                if ($cont == 1) {
                    $departure_1 = $itinerary->FlightSegment["DepartureAirport"];
                    $arrival_1 = $itinerary->FlightSegment["ArrivalAirport"];
                }elseif ($cont == 2) {
                    $departure_2 = $itinerary->FlightSegment["DepartureAirport"];
                    $arrival_2 = $itinerary->FlightSegment["ArrivalAirport"];
                }
            }
            switch ($cont) {
                case 1:
                    $itinerary = $departure_1." ".$arrival_1;
                break;
                case 2:
                    if (trim($arrival_1) == trim($departure_2)) {
                        $itinerary = $departure_1." ".$arrival_1." ".$arrival_2;
                    }else{
                        $itinerary = $departure_1." ".$arrival_1." ".$departure_2." ".$arrival_2;
                    }
                break;
                default:
                    $itinerary = "NULL";
                break;
            }
            return $itinerary;
		}

		public static function cod_method_payment_search($ticket){//Función buscar el nombre del método de pago del boleto
			foreach ($ticket->FormsOfPayment->FormOfPayment as $code) {
				foreach (self::cod_method_payment() as $key_code => $value_code) {
					if ($code["Code"] == $key_code) {
						$method = $value_code;
					}
				}
			}
			return $method;
		}

		public static function coupon_status_search($ticket){//Función buscar el nombre del estatus del cupón
			foreach ($ticket->FlightReference->FlightSegment->CouponInfo as $coupon) {
				foreach (self::cod_coupon_status() as $key_coupon => $value_coupon) {
					if ($coupon["CouponStatus"] == $key_coupon) {
						$coupon_status = $value_coupon;
					}
				}
			}
			return $coupon_status;
		}

		public static function process_ticketNumber($ticket){//Función que le anexa al número de ticket el guión después de los primeros 3 caracteres
			$primeros_tres = substr($ticket->TicketItemInfo["TicketNumber"], 0,3);
			$ultimos = substr($ticket->TicketItemInfo["TicketNumber"], 3);
			$TicketNumber = $primeros_tres."-".$ultimos;
			return $TicketNumber;
		}

		public static function process_tipo_vuelo($codigo){//Función que traduce el tipo de vuelo
			if ($codigo == "I") {
                $tipo_vuelo = "Internacional";
            }elseif ($codigo == "D") {
                $tipo_vuelo = "Nacional";
            }
			return $tipo_vuelo;
		}

		public static function process_localizador($ticket){//Función que traduce el tipo de vuelo			
			$localizador=$ticket->BookingReferenceID["ID"];
			$status=self::status_emission_name_search($ticket);
            if ($status=="Emitido") 						$localizador= $localizador."(E)";
            if ($status=="Anulado")  						$localizador= $localizador."(A)";         	
            if (self::original_ticket_search($ticket)>0) 	$localizador= $localizador."(R)";    //si es una reemision	        
            return $localizador;
		}

		public static function process_date($ticket,$TimestampGMT){//Función que valida la fecha de creacion para emision y reeemision			
			$creationDate=$ticket->BOD->Source["PNR_DateOfCreationGMT"].":00";			
			//Restamos 3 horas y media por diferencia horaria de servidores Kiu.
			$nuevafecha = strtotime ('-3 hour',strtotime($creationDate));
			$nuevafecha = date ( 'Y-m-d H:i:s' , $nuevafecha );
			$nuevafecha = strtotime ('-30 minute',strtotime($nuevafecha));
			$nuevafecha = date ( 'Y-m-d H:i:s' , $nuevafecha );
			if (self::status_emission_name_search($ticket)=="Reemitido"){
            	$nuevafecha=$TimestampGMT;
            }
			return $nuevafecha;
		}
	}
	
?>