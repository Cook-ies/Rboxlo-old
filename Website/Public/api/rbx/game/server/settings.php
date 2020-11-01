<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/RBX.php");
    header("Content-Type: application/json");
    
    // unlike other Settings/QuietGet endpoints, this *will* check if there is an apiKey
    // if there isn't, it errors

    $version = "2017"; // default
    
    if (isset($_GET["apiKey"]))
    {
        $key = get_api_key_info($_GET["apiKey"]);
        $version = $key["version"] ?? $version;
    }
    else
    {
        exit(json_encode(["success" => false, "message" => "Invalid API key"]));
    }

    exit(get_fflags($version, "RCCService"));
?>