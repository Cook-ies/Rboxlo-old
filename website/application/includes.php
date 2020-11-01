<?php
	require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Environment/Project.php");
	require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Environment/Google.php");
	require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Environment/Repository.php");
	
	require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Functions.php");
	
	require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Main.php");
	require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Database.php");
	
	require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/HTML.php");
	
	// Disallow access to pages with ".php"
	if (ends_with(substr($_SERVER["REQUEST_URI"], 0, strpos($_SERVER["REQUEST_URI"], "?")), ".php"))
	{
		require_once($_SERVER["DOCUMENT_ROOT"] . "/../Public/error/404.php");
		exit();
	}
?>