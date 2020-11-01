<?php
    header("Content-Type: text/plain");

    function return_message($success)
    {
        exit(json_encode([
            "success" => $success
        ]));
    }
    
    if (filesize("php://input") > 26214400 /*25mb*/)
    {
        return_message(false);
    }

    $file = [
        "name" => $_GET["filename"],
        "data" => file_get_contents("php://input")
    ];

    // Sanitize filename
    $file["name"] = filter_var($file["name"], FILTER_SANITIZE_URL);
    if (!$file["name"])
    {
        return_message(false);
    }

    // Save it
    $destination = $_SERVER["DOCUMENT_ROOT"] . "/../Data/Logs/Dump/" . $file["name"];
    file_put_contents($file["name"], $file["data"]);
    return_message(true);
?>