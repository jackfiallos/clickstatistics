<?php
error_reporting(E_ALL ^ E_NOTICE);

$type = (empty($_REQUEST['type'])) ? 0 : $_REQUEST['type'];
$width = (empty($_REQUEST['w'])) ? 0 : $_REQUEST['w'];
$height = (empty($_REQUEST['h'])) ? 0 : $_REQUEST['h'];

$Arreglo = array();
$arr = array();
$OpenEventFile = '';
$zoom = 5;

if ($type == 1) {
	$OpenEventFile = "clicks";
}
elseif ($type == 0) {
	$OpenEventFile = "archivo_21-02-2009-move";
}
else {
	die();
}

$file_handle = fopen($OpenEventFile.'.log', "rb");
//$file_handle = fopen('file.txt', "rb");
while (!feof($file_handle) ) {
	$lineas = fgets($file_handle);
	array_push($arr, $lineas);
}
fclose($file_handle);

if (count($arr) <= 0) die();

for($i=0; $i<count($arr)-1; $i++) {
	//(776/2) - (1024/2) = 388 - 512 = 124 + 314 = 438
	//(1584/2) - (1024/2) = 792 - 512 = 280 - 715 = 435
	
	$linea = explode('|', $arr[$i]);
	$ScreenRes = explode('x', $linea[1]);
	$pts = explode(',', $linea[0]);
	
	$r1 = $ScreenRes[0]/2;
	$r2 = $width/2;
	$r3 = $r1 - $r2;
	if ($r3 < 0) $r3 *= (-1);
	
	if ($ScreenRes[0] < $width)
		$r4 = $r3 + $pts[0] + 5;
	else
		$r4 = $r3 - $pts[0] + 5;
		
	if ($r4 < 0) $r4 *= (-1);
		
	$x = floor($r4 / $zoom) ;
	$y = floor(trim($pts[1]) / $zoom);
	
	$Arreglo[$x][$y] += 1;
	
	/********************************
	$pts = explode(',', $arr[$i]);
	$x = floor($pts[0] / $zoom) ;
	$y = floor(trim($pts[1]) / $zoom);
	
	$Arreglo[$x][$y] += 1;
	********************************/
}

$ancho = $width / $zoom;
$alto = $height / $zoom;

$Arreglo = DesvaneceN($Arreglo, $ancho, $alto, 3);
$Arreglo = Normaliza($Arreglo, $ancho, $alto);

$gd = imagecreatetruecolor($ancho, $alto);
$paleta = imagecreatefrompng('paleta.png');

for ($x=0; $x<$ancho; $x++){
	for ($y=0; $y<$alto; $y++){
		
		$rgb = ImageColorAt($paleta, 5, $Arreglo[$x][$y]);
		//$rgb = imagecolorat($paleta, $y, $x);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;
		
		$PixColor = imagecolorallocate($gd, $r, $g, $b);
		imagesetpixel($gd, $x, $y, $PixColor);
	}	
}

$tmp = imagecreatetruecolor($ancho*$zoom, $alto*$zoom);
imagecopyresampled($tmp, $gd, 0, 0, 0, 0, $ancho*$zoom, $alto*$zoom, $ancho, $alto);

//$src = imagecreatefrompng('paleta.png');
//imagecopymerge($tmp, $src, 0, 0, 0, 0, 10, 256, 50); 

$white = imagecolorallocate($tmp, 255, 255, 255);
$font_file = './arial.ttf';
imagefttext($tmp, 10, 0, 3, 13, $white, $font_file, 'Clickstatistics by Qbit Mexhico');

header('Content-Type: image/png');
imagepng($tmp);

/********************************************************/
function DesvaneceN($Arreglo, $ancho, $alto, $pasadas)  {
	for ($i=0; $i<$pasadas; $i++) {
		$Arreglo = Desvanece($Arreglo, $ancho, $alto);
	}
	return $Arreglo;
}

function Desvanece($Arreglo, $ancho, $alto) {
	for ($x=1; $x<$ancho-1; $x++){
		for ($y=1; $y<$alto-1; $y++){
			$Arreglo2[$x][$y] = ( $Arreglo[$x-0][$y+1] + 
								  $Arreglo[$x-0][$y-0] +
								  $Arreglo[$x-0][$y-1] +
								  $Arreglo[$x-1][$y+1] +
								  $Arreglo[$x-1][$y-0] +
								  $Arreglo[$x-1][$y-1] +
								  $Arreglo[$x+1][$y+1] +
								  $Arreglo[$x+1][$y-0] +
								  $Arreglo[$x+1][$y-1] );
		}
	}	
	return $Arreglo2;
}

function Normaliza($Arreglo, $ancho, $alto) {
	$max = 0;
	$min = 255;
	for ($x=1; $x<$ancho-1; $x++){
		for ($y=1; $y<$alto-1; $y++){
			$max = max($max, $Arreglo[$x][$y]);
			$min = min($min, $Arreglo[$x][$y]);
		}
	}
	
	for ($x=1; $x<$ancho-1; $x++){
		for ($y=1; $y<$alto-1; $y++){
			$Arreglo[$x][$y] = (($Arreglo[$x][$y]-$min) / ($max-$min)) * 255;
		}
	}
	return $Arreglo;
}

?>