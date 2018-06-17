<?php
require_once 'plugconfig.inc.php';

if (!isset($issetlink)) {
    $issetlink = false;
}

/***********************
Abre la conexion a la BD
***********************/
function openDatabase() {
	global $config;
	global $issetlink;
	
	if($issetlink) 
    	return true;
	
	$issetlink = mysql_connect($config['db']['server'],$config['db']['user'],$config['db']['pass']) or die('Error #1: ' . mysql_error());
	$database = mysql_select_db($config['db']['name'], $issetlink) or die('Error #2: ' . mysql_error());
	
	return true;
}

/***********************
Inserta resultados a la BD
***********************/
function insertData($arr, $fromclick = true) {
	global $config;
	
	if ($fromclick) {
		$sql = "INSERT INTO datareg (coord, srcres, uri, eventtype, regdate) 
				VALUES (
					'".$arr['coord']."', 
					'".$arr['srcres']."', 
					'".$arr['uri']."', 
					'click', 
					'".date('Y-m-d')."')";
	}
	else {
		$sql = "INSERT INTO datareg (coord, srcres, uri, eventtype, regdate) VALUES "; 
		foreach($arr as $clave => $valor) {
			foreach($valor as $key => $value) {
				$sql .= "(
					'".$valor['coord']."', 
					'".$valor['srcres']."', 
					'".$valor['uri']."', 
					'move', 
					'".date('Y-m-d')."'),";
			}
		}
		$sql = substr_replace($sql, ';', strlen($sql)-1);
	}
	
	openDatabase();
	$query = mysql_query($sql) or die('Error: '.mysql_error());
	
	return true;
}

function getResultPages() {
	global $config;
	
	$sql = "SELECT uri FROM datareg;";
	openDatabase();
	$query = mysql_query($sql) or die('Error: '.mysql_error());
	
	$result = array();
	if (mysql_num_rows($query) > 0) {
		while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
			$uriParts = explode("/", $row['uri']);
			$uri = $uriParts[count($uriParts)-1];
			if (!in_array($uri, $result))
				array_push($result, $uri);
		}
	}
	
	return $result;
}

function getResultDates($event) {
	global $config;
	
	$eventString = "";
	switch($event) {
		case 1:
			$eventString = "move";
			break;
		default:
			$eventString = "click";
			break;
	}
	
	$sql = "SELECT eventtype, regdate FROM datareg WHERE eventtype = '".$eventString."';";
	openDatabase();
	$query = mysql_query($sql) or die('Error: '.mysql_error());
	
	$result = array();
	if (mysql_num_rows($query) > 0) {
		while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
			$regdateParts = explode("-", $row['regdate']);
			$regdate = $regdateParts[2]."-".$regdateParts[1]."-".$regdateParts[0];
			$eventtype = $row['eventtype'];
			$filename = "archivo_".$regdate."-".$eventtype.".log";
			
			if (!in_array($filename, $result))
				array_push($result, $filename);
		}
	}
	
	return $result;
}

function getResultData($page, $type, $file) {
	global $config;
	
	// tomar todas las uri registradas y mediante el valor $page filtrar la pagina
	$uriPages = getResultPages();
	$uriPages[$page];
	// realizar la consulta por parametro de type donde click = 0 y move = 1
	
	// filtrar la consulta de acuerdo a file que es igual a date en la BD
	
	$sql = "SELECT * FROM datareg;";
	openDatabase();
	$query = mysql_query($sql) or die('Error: '.mysql_error());
	
	$result = array();
	if (mysql_num_rows($query) > 0) {
		while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
			$data = $row['coord'].",".$row['srcres'].",".$row['uri'];
			array_push($result, $data);
		}
	}
	
	return $result;
}

?>