<?php
$config = array(
	'db'=>array(
		'usedb'=>false,
		'user'=>'root',
		'pass'=>'',
		'name'=>'clickstatistics',
		'server'=>'localhost'
	),
	'files'=>array(
		'period'=>7,	//Define el tiempo que se guardara la info en un archivo antes de crear otro (dias)
		'trash'=>4		//Define despues de cuantos archivos se eliminan los pasados
	),
	'config'=>array(
		'uri'=>'templates/',
		'user'=>'jack',
		'pass'=>'jack'
	),
	'events'=>array(
		'click',
		'move'
	),
	'sections'=>array(
		parse_ini_file("plugconfig.ini", true)
	)
);
?>