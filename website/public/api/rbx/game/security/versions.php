<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../application/rbx.php");
    header("Content-Type: application/json");
    
    $key = get_api_key_info($_GET["apiKey"]) ?? ["usage" => null];

    if ($key["usage"] !== "get_security_information")
    {
        exit(json_encode([
            "Message" => "No HTTP resource was found that matches the request URI " . get_server_host() . "/" . $_SERVER["REQUEST_URI"] ."'."
        ]));
    }

    open_database_connection($sql);

    $data = [];

    close_database_connection($sql, $statement);

    exit(json_encode([
        "data" => $data
    ]));
?>