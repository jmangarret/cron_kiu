<?php
class zip{
	public static function extract_files($zip_file){
		set_time_limit(0); //Tiempo de ejecución ilimitado (30s por defecto)	
    	$zip = new ZipArchive;
		if ($zip->open($zip_file) === TRUE){
			$zip->extractTo('C:/inetpub/wwwroot/eRetail/AmadeusLATAM.EretailAdapter.ServiceWCF/cron/xml');
			$zip->close();
			$extraction = TRUE;
    	}else{
    		$extraction = FALSE;
    	}
	return $extraction;
	}
}
?>