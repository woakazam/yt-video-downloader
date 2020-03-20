<?php

	session_start();
	date_default_timezone_set('Europe/Istanbul');

	define ('VERS', '1.0.0');
	define ('MAIN', '/');
	define ('NAME', $_SERVER['SERVER_NAME']);
	define ('ROOT', $_SERVER['DOCUMENT_ROOT']);
	
	require_once (ROOT. MAIN. 'app/system/cdn.php');
	
	function LinkAssets($data){
		return '//'. NAME. MAIN. 'assets/'. $data. '?v='. VERS;
	}
	
	function returnJSON($data){
		header("Content-type: application/json; charset=utf-8");
		echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		exit();
	}