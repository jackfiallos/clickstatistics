<?php
include('includes/base.inc.php');

// utilizado cuando se enviaron coordenadas desde mouseClick
$x = (int)(empty($_REQUEST['x'])) ? 0 : $_REQUEST['x'];
$y = (int)(empty($_REQUEST['y'])) ? 0 : $_REQUEST['y'];

// utilizado cuando se enviaron coordenadas desde mouseMove
$arr = (empty($_REQUEST['a'])) ? 0 : $_REQUEST['a'];

// resolucion del visitante
$res = trim($_REQUEST['res']);

if (empty($arr)) {  //CLICK
	// utilizar la generacion de archivos
	if (!$config['db']['usedb']) {
		$apuntador = @fopen('logs/archivo_'.date('d-m-Y').'-click.log', 'a');
		if (is_resource($apuntador)) {
			$srcRes = explode("x", $res);
			if (@fputs($apuntador, $x.','.$y.'|'.$srcRes[0].'x'.$srcRes[1].'|'.$_SERVER['HTTP_REFERER']."\n") === false)
				die("No se puede escribir sobre el archivo");
			else {
				fclose($apuntador);
			}
		}
		else
			die("No se puede abrir o crear el archivo.");
	}
	// utilizar el motor de BD
	else {
		$srcRes = explode("x", $res);
		$arrInsert = array(
			'coord'=>$x.','.$y,
			'srcres'=>$srcRes[0].'x'.$srcRes[1],
			'uri'=>$_SERVER['HTTP_REFERER']
		);
		
		insertData($arrInsert);
	}
}
else {  // MOVE
		$coords = explode(",", $arr);
		$narray = array();
		
		$n = 0;
		for ($i=0; $i<(count($coords)/2); $i++) {
			array_push($narray,$coords[$n].','.$coords[$n+1]);
			$n += 2;
		}
		$data = array();
		foreach($narray as $clave => $valor){
			$farray = explode(",", $valor);
			$apuntador = @fopen('logs/archivo_'.date('d-m-Y').'-move.log', 'a');
			// utilizar la generacion de archivos
			if (!$config['db']['usedb']) {
				$srcRes = explode("x", $res);
				if (is_resource($apuntador)) {
					if (@fputs($apuntador, $farray[0].','.$farray[1].'|'.$srcRes[0].'x'.$srcRes[1].'|'.$_SERVER['HTTP_REFERER']."\n") === false)
					if (@fputs($apuntador, $farray[0].','.$farray[1]."\n") === false)
						die("No se puede escribir sobre el archivo");
					else {
						fclose($apuntador);
					}
				}
				else
					die("No se puede abrir o crear el archivo.");
			}
			// utilizar el motor de BD
			else {
				$srcRes = explode("x", $res);
				$arrInsert = array(
					'coord'=>$farray[0].','.$farray[1],
					'srcres'=>$srcRes[0].'x'.$srcRes[1],
					'uri'=>$_SERVER['HTTP_REFERER']
				);
				array_push($data, $arrInsert);
			}
		}
		
		// si se utiliza el motor de BD, insertar datos
		if ($config['db']['usedb']) {
			if (count($data) > 0)
				insertData($data, false);
		}
}
?>
