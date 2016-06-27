<?php
//error_reporting(E_ALL);
  //      ini_set('display_errors', '1');


//include "items.php";//Incluir la clase items
include "Extract.php";//Incluir la clase zip
include "sentencias.php";//Incluir la clase registro
include "config.php";//Incluir la clase registro

registro::conectar_db();
/*PARCHE PARA PASAR PROCESADOS A CARPETAS MENSUALES POR AÑO jmangarret 22jun2016*/
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
/*FIN PARCHE PARA PASAR PROCESADOS A CARPETAS MENSUALES*/

$zip_drive = opendir($drive_folder); //Ruta de los ZIP de Google Drive


while ($archivo_zip = readdir($zip_drive)) {

    $registro_zip = registro::consultar_registro($tabla_zip);

    $zip_file = $drive_folder."/".$archivo_zip;
    $punto = explode(".", $archivo_zip); //Capturar la extención del archivo
    $extension_zip = end($punto); 

    if($extension_zip == "zip"){

      $fecha_zip = date ("Y-m-d H:i:s.", filemtime($zip_file));

      $zip_registrado = registro::validar_registro($registro_zip,$archivo_zip);

        if ($zip_registrado == FALSE){

          if (zip::extract_files($zip_file)){

          $zip_query = registro::insertar($archivo_zip, $fecha_zip, $tabla_zip);

          if (mysql_query($zip_query)){

            $id_zip = registro::consultar_ultimo_id($archivo_zip, $fecha_zip, $tabla_zip);

            if ($id_zip = mysql_query($id_zip)){
              $id_zip = mysql_fetch_array($id_zip);
              echo "/*--------------------------------------------*/<br>";
              echo "Archivo ZIP de entrada: ".$archivo_zip."</br>";
              echo "Identificador: ".$id_zip["id"]."<br>";
              echo "/*--------------------------------------------*/<br>";
              $confirmar_zip = TRUE;
              rename ($drive_folder."/".$archivo_zip,$drive_folder_processed."/".$archivo_zip);
            }
          }else{
            echo "/*--------------------------------------------*/<br>";
            echo "ALERTA! Archivo ZIP no registrado en la base de datos</br>";
            printf("Código de error: %d\n", mysql_errno().'<br>');
            printf("Error message: %s\n", mysql_error().'<br>');
            echo "/*--------------------------------------------*/<br>";
            $fecha_error = date("Y-m-d H:i:s.");
            $log = registro::logs(mysql_errno(), mysql_error(), "Archivo ZIP no insertado en la base de datos", $fecha_error);
            mysql_query($log);
          }
          }else{
            $fecha_error = date("Y-m-d H:i:s.");
            $log = registro::logs("Ejecucion interrumpida", "Ejecucion interrumpida", "Error al extraer archivo", $fecha_error);
            mysql_query($log);
            die("Error al extraer archivo");
          }
        }elseif($registro_zip == NULL){

          if (zip::extract_files($zip_file)){

          $zip_query = registro::insertar($archivo_zip, $fecha_zip, $tabla_zip);

          if (mysql_query($zip_query)){

            $id_zip = registro::consultar_ultimo_id($archivo_zip, $fecha_zip, $tabla_zip);

            if ($id_zip = mysql_query($id_zip)){
              $id_zip = mysql_fetch_array($id_zip);
              echo "/*--------------------------------------------*/<br>";
              echo "Primer archivo ZIP de entrada: ".$archivo_zip."</br>";
              echo "Primer Identificador: ".$id_zip["id"]."<br>";
              echo "/*--------------------------------------------*/<br>";
              $confirmar_zip = TRUE;
              rename ($drive_folder."/".$archivo_zip,$drive_folder_processed."/".$archivo_zip);
            }
          }else{
            echo "/*--------------------------------------------*/<br>";
            echo "ALERTA! Archivo ZIP no registrado en la base de datos</br>";
            printf("Código de error: %d\n", mysql_errno().'<br>');
            printf("Error message: %s\n", mysql_error().'<br>');
            echo "/*--------------------------------------------*/<br>";
            $fecha_error = date("Y-m-d H:i:s.");
            $log = registro::logs(mysql_errno(), mysql_error(), "Archivo ZIP no insertado en la base de datos", $fecha_error);
            mysql_query($log);
          }

          }else{
            $fecha_error = date("Y-m-d H:i:s.");
            $log = registro::logs("Ejecucion interrumpida", "Ejecucion interrumpida", "Error al extraer archivo", $fecha_error);
            mysql_query($log);
            die("Error al extraer archivo");
          }
        }
    }
}



/*----------------------------------------------------------*/
/*Registro de los boletos en la base de datos*/
/*----------------------------------------------------------*/

$directorio = opendir($folder); //ruta actual

while ($archivo = readdir($directorio)) //Obtenemos un archivo y luego otro sucesivamente
{
  $registro_xml = registro::consultar_registro($tabla_xml);

        $xml_file = $folder."/".$archivo;
        $trozos = explode(".", $archivo); //Capturar la extención del archivo
        $extension = end($trozos); 
        
        if ($extension == "XML"){

            if (file_exists($xml_file)) {

            $fecha = date ("Y-m-d H:i:s.", filemtime($xml_file));//Extraigo fecha de modificación como String

            $xml = simplexml_load_file($xml_file);//Convertir en Objeto.
            $TimestampGMT = $xml["TimestampGMT"];//Fecha de emisión

            $es_kiu = strpos($archivo, "KIU_BOX");

            $xml_registrado = registro::validar_registro($registro_xml,$archivo);

                if($xml_registrado == FALSE)
                  {
                    
                    $xml_query = registro::insertar($archivo, $fecha, $tabla_xml);

                    if (mysql_query($xml_query)) {

                      $consultar_id = registro::consultar_ultimo_id($archivo, $fecha, $tabla_xml);

                      if($consultar_id = mysql_query($consultar_id)){
                          if (mysql_num_rows($consultar_id) > 0)
                              {
                                  $buff_id_xml = mysql_fetch_array($consultar_id);
                                  echo "/*--------------------------------------------*/<br>";
                                  echo"Boleto KIU de entrada: ".$archivo."</br>";
                                  echo "Identificador: ".$buff_id_xml["id"]."<br>";
                                  echo "/*--------------------------------------------*/<br>";
                                  $confirmar_xml = TRUE;
                                  rename ($folder."/".$archivo, $xml_folder_processed."/".$archivo);
                              }
                          else
                              {
                                  echo "/*--------------------------------------------*/<br>";
                                  echo "No se encontró el ID del XML<br>";
                                  echo "/*--------------------------------------------*/<br>";
                                  $fecha_error = date("Y-m-d H:i:s.");
                                  $log = registro::logs(mysql_errno(), mysql_error(), "No se encontro el ID del XML", $fecha_error);
                                  mysql_query($log);
                              }
                      }
                      // Recorremos el XML
                      foreach ($xml->KIU_TktDisplay as $ticket) {//FOREACH Recorrer todos los datos del boleto

                        $qry = registro::recorrer_xml($ticket, $buff_id_xml["id"], $TimestampGMT);
                  
                      if (!mysql_query($qry)) {
                          echo "/*--------------------------------------------*/<br>";
                          printf("Código de error: %d\n", mysql_errno().'<br>');
                          printf("Error message: %s\n", mysql_error().'<br>');
                          echo "/*--------------------------------------------*/<br>";
                          $fecha_error = date("Y-m-d H:i:s.");
                          $log = registro::logs(mysql_errno(), mysql_error(), "Datos del boleto XML no insertado", $fecha_error);
                          mysql_query($log);
                         }
                      }
                    }
                  }
                  else
                  {
                    if($registro_xml == NULL){ //En caso de que sea el primer registro en la base de datos

                      $xml_query = registro::insertar($archivo, $fecha, $tabla_xml);

                      if (mysql_query($xml_query)) {

                        $consultar_id = registro::consultar_ultimo_id($archivo, $fecha, $tabla_xml);
                        
                        if($consultar_id = mysql_query($consultar_id)){
                            if (mysql_num_rows($consultar_id) > 0)
                                {
                                    $buff_id_xml = mysql_fetch_array($consultar_id);
                                    echo "/*--------------------------------------------*/<br>";
                                    echo"Primer boleto KIU de entrada: ".$archivo."</br>";
                                    echo "Primer Identificador: ".$buff_id_xml["id"]."<br>";
                                    echo "/*--------------------------------------------*/<br>";
                                    $confirmar_xml = TRUE;
                                    rename ($folder."/".$archivo, $xml_folder_processed."/".$archivo);
                                }
                            else
                                {
                                    echo "/*--------------------------------------------*/<br>";
                                    echo "No se encontró el ID del XML<br>";
                                    echo "/*--------------------------------------------*/<br>";
                                    $fecha_error = date("Y-m-d H:i:s.");
                                    $log = registro::logs(mysql_errno(), mysql_error(), "No se encontro el ID del XML", $fecha_error);
                                    mysql_query($log);
                                }
                        }
                        // Recorremos el XML
                        foreach ($xml->KIU_TktDisplay as $ticket) {//FOREACH Recorrer todos los datos del boleto

                          $qry = registro::recorrer_xml($ticket, $buff_id_xml["id"], $TimestampGMT);

                        if (!mysql_query($qry)) {
                            echo "/*--------------------------------------------*/<br>";
                            printf("Código de error: %d\n", mysql_errno().'<br>');
                            printf("Error message: %s\n", mysql_error().'<br>');
                            echo "/*--------------------------------------------*/<br>";
                            $fecha_error = date("Y-m-d H:i:s.");
                            $log = registro::logs(mysql_errno(), mysql_error(), "Datos del boleto XML no insertado", $fecha_error);
                            mysql_query($log);
                           }
                        }
                      }
                    }
                  }
              }
      }        
    }
    if (($confirmar_zip == TRUE) && ($confirmar_xml == TRUE)) {
      echo "Registros de boletos exitosos";
    }
mysql_close();
?>