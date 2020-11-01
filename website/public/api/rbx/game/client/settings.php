<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/RBX.php");
    header("Content-Type: application/json");
    
    $version = "2017"; // default
    
    if (isset($_GET["apiKey"]))
    {
        $key = get_api_key_info($_GET["apiKey"]);
        $version = $key["version"] ?? $version;
    }

    exit(get_fflags($version, "ClientAppSettings"));
?>