<?php

include('includes/base.inc.php');
include('includes/plugconfig.inc.php');

error_reporting(E_ALL ^ E_NOTICE);

$page = (empty($_REQUEST['page'])) ? 1 : $_REQUEST['page'];
$type = (empty($_REQUEST['type'])) ? 0 : $_REQUEST['type'];
$file = (empty($_REQUEST['file'])) ? 0 : $_REQUEST['file'];
$width = (empty($_REQUEST['w'])) ? 0 : $_REQUEST['w'];
$height = (empty($_REQUEST['h'])) ? 0 : $_REQUEST['h'];

$filesConfig = parse_ini_file("includes/plugconfig.ini",true);

$pages = $filesConfig['Pages'];
$design = $filesConfig['Design'];
$Arreglo = array();
$arr = array();
$zoom = 6;

$ancho = $width / $zoom;
$alto = $height / $zoom;

$result = array();

// si se ha seleccioando guardar en archivos
if (!$config['db']['usedb']) {
	// escaneo todos los archivos
	$archivos = array_diff(scandir("logs//"), Array(".", ".."));
	
	// la lista de archivos son recorridos uno a uno
	foreach($archivos as $claveFile => $valorFile) {
		// si el archivo tiene extension .log
		if (substr($valorFile, strlen($valorFile)-3, strlen($valorFile)) == 'log')
		{
			// si la cadena del archivo contiene la palabra click
			if (($config['events'][$type] == 'click') && (strstr($valorFile, 'click'))) //Dato del evento click
			{
				$cstring = strstr($valorFile, 'click');
				// agrego el nombre del archivo al arreglo
				array_push($result, $valorFile);
			}
			// si la cadena del archivo contiene la palabra move
			elseif (($config['events'][$type] == 'move') && (strstr($valorFile, 'move'))) //Dato del evento move
			{
				$cstring = strstr($valorFile, 'move');
				// agrego el nombre del archivo al arreglo
				array_push($result, $valorFile);
			}
		}
	}
	//echo $result[$file];
	// segun la variable enviada por la url $file se procede a abrir ese archivo
	$file_handle = fopen("./logs/".$result[$file], "rb");
	// se recorren las lineas
	while (!feof($file_handle) ) {
		// se obtiene el contenido de la linea
		$lineas = fgets($file_handle);
		// se guarda en un arreglo el contenido de la linea
		array_push($arr, $lineas);
	}
       
	// se cierra el archivo
	fclose($file_handle);
}
// si se ha seleccionado guardar en la BD
else {
	// se obtiene los resultados desde la BD
	$bdResult = getResultData($page, $type, $file);
	foreach($bdResult as $clave => $valor) {
		array_push($arr, $valor);
	}
}
/***************************************************
** @jackfiallos - de aqui hacia abajo no meter mano
***************************************************/
if (count($arr) < 1) die();

for($i=0; $i<count($arr)-1; $i++) {
	$linea = explode('|', $arr[$i]);
	//die($linea[2]."* -".$pages[$page]);
	if (strpos($linea[2], $pages[$page])!=false) {
		die(":_D");
		$ScreenRes = explode('x', $linea[1]);
		$pts = explode(',', $linea[0]);

		$r1 = $ScreenRes[0]/2; // resolucion del cliente
		$r2 = $width/2; // resolucion del administrador
		$r3 = $r1 - $r2; // diferencia de tamaños de pixeles
		
		if ($design[$page] == 'center') { //centered
			if ($r3 < 0)
				$r3 *= (-1);

			$r4 = ($ScreenRes[0] < $width) ? $r3 + $pts[0] + 5 : $r3 - $pts[0] + 5;

			if ($r4 < 0)
				$r4 *= (-1);

			$x = floor($r4 / $zoom) ;
			$y = floor(trim($pts[1]) / $zoom);
		}
		else { // fluid
			if ($pts[0]>(round($ScreenRes[0]/2))) {
				$r4 = $r3 - ($pts[0]-$r1);
				//--if ($r4 < 0) $r4 = $r4 * (-1) ;
				$r5 = $r4 - $r2;
				if ($r5 < 0) $r5 = $r5 * (-1) ;
			}
			else
				$r5 = $pts[0];

		       $x = floor($r5 / $zoom) ;
		       $y = floor(trim($pts[1]) / $zoom);
		}

		$Arreglo[$x][$y] += 1;
	}
}

if (count($Arreglo) < 1) {
	$im = imagecreatefromstring(base64_decode("iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAFnRFWHRDcmVhdGlvbiBUaW1lADA0LzA3LzA2niZe/wAAAAd0SU1FB9cDDAgaFoRzXW0AAAAJcEhZcwAACxEAAAsRAX9kX5EAAAAEZ0FNQQAAsY8L/GEFAAAFN0lEQVR42u1Wa0zTVxQ/9EFLKa8C5S0UFCgilIAyEJXHXsSBAlkyMW7MuZdxDxM/LEu2L/PDssTFLy6ZWfb4IMvUMI0mYjI3NgSZG6y8JikPHa9BsRTb0lKgsN/909ZSWigf9s2TnNz7v/9zzu+eex73Ej2hJ/Q/kZ9jsry87F3Izy8QQzZ4H1gFloH5dl4AD4PbwU1gDWwteLHjGzAEhRjywYcDRIE5YdLISKkkNEIsDAgUCkT8pWUbzc2brYu2hdlHJt30jOnh+Pyi9RbkL4CHlt2M+gRsBz0hFPjXRMu2pCuileHKxDxSJuZSbHgSSQNCOLlpo5aGJzXU8+AuaUbUi8Pafp1WP9qBX1/BZsOmgCHAjvJdeFmriMlI3pddyX+h4BWKCktYN26a0U663PQFqQduW0anBjvg/TksX3Yc/brA+MnidiJQHPxaRlKesmbvW4LSnGri8fg+Jc38whxdv/MdNTSfNw+N97YD/KzDc1dgngfdfIlIeigtQaV8u/ITwdO5L/oMyshfKKb9BS/TwaJjkgT5tjws1QEwyV1uFbA9rnXysPjtRTv2C7JSCh8f44h6Q9B2TRM3ioQBVLXnddqZXhqA8DDwI+sCg3KDA2VFsREKKdu1gz6/eJJqT+dwbDTPrAFkayUnw+jNMyWcLCM+T0CVu49SbESS3F8gKoVTKesBVwRLwuSZSbsoPDjaabT+1lmn1yx+7sTWHBtiso751rgdhOTkwxmWlSUegbEjMYsvyiS0MLPcKdCPTF3lncWDx25rDh2eH4+YEwBmVaLy5nEMYhOFbOazWnUuomY3S646rPbRcCSYrjLkChwu4AslfGQwi89jI4mUm1rMzYMkoVRRULcGyHWNyTIdB8VFKAhdToSpxFVH4DJnhey3TGtb55njV+ha67dUrDq4yqjr5uo/UlOT+grVlr2/6h8rLx6P88/PG7DOtrSoh7cK64KFKwkHsfZ4qOy9dY83NT6bY3ea1I+gqVhttHKZeASeQsOfAKjtwUQfPy0hZ42R+//eo1/UP1LPUBvNzOpoaclGEnEQJcdk0N7sSlJtLSKUziodzUgnWawmA6ZjHoHR1maR2b0my6On0GtlrsBoe/RT+yVq/P0C6U1TXA442p95zsh6M3VjMwXbn6Mjz54iJKhT96/+38g0Z2DAXd48ZtSsM0xWdA62ysp3HWZlYN+1mhrv1hOuPcpKLiR0NUqMTuO80xkmqKXnBt3uuk6tvY2ErkfVe97g9Ea0A9zFYTTrJ/H5qyuQewNpnTZMtuOaMzd1XnUudg62sHuXA3yn+lPOM3Y1RoTEEDuZuuc/oFfLP0ReiDnvGTH5huYvGfhDi3W2FUvdXj3GcetwhJcgnIlulB0jS2T9lrbIU4ndUPnKZ0jsLyF3Yo2CbUY7M0ooSW6NheaPvp/n9UZtDz7r2dW40bUoxXAqVBpxLFORH3f8wGlKTVCRrwTvqLnrGl1sOmcbGOvuRs58Btvf2217B7YLsOB+LAuOOpCekBNfnFMlKFFVUVhQpFdApj+uu09XW76mtt6b1n8mNfdm5wzf0MpLxOwTsF0oCsNR1HANYpm2Rb5NujO9jLJSCgjPIK6MGME49Q130J3em1xmo26n8fT5E5XwA628PgwuNjcGtgsy6+xWeQkZno3jl6PvBov8A/wloiBCw+FeHLNzRgs2YNIbp8bQB5ohfx72ejzY8w3YRYG97HaDy8BKcAgegYKFxXmmtAjWgv8G3wC3wZbNi53NAXswEI6BhYKVoxa6Wh/1nPP/AKo6IT+TQwRVAAAAAElFTkSuQmCC"));
	header('Content-Type: image/png');
    imagepng($im);
    imagedestroy($im);
	die();
}


/*
* Codigo por @zerugiran
*/
$Arreglo = DesvaneceN($Arreglo, $ancho, $alto, 3);
$Arreglo = Normaliza($Arreglo, $ancho, $alto);

$gd = imagecreatetruecolor($ancho, $alto);
$paleta = imagecreatefrompng('includes/paleta.png');

for ($x=0; $x<$ancho; $x++){
	for ($y=0; $y<$alto; $y++){
		$rgb = ImageColorAt($paleta, 5, $Arreglo[$x][$y]);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;

		$PixColor = imagecolorallocate($gd, $r, $g, $b);
		imagesetpixel($gd, $x, $y, $PixColor);
	}	
}

$tmp = imagecreatetruecolor($ancho*$zoom, $alto*$zoom);
imagecopyresampled($tmp, $gd, 0, 0, 0, 0, $ancho*$zoom, $alto*$zoom, $ancho, $alto); 

$white = imagecolorallocate($tmp, 255, 255, 255);
$font_file = 'includes/arial.ttf';
imagefttext($tmp, 10, 0, 3, 13, $white, $font_file, 'Clickstatistics by Qbit Mexhico');

header('Content-Type: image/png');
imagepng($tmp);
?>