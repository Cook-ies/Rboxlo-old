<?php
    header("Content-Type: application/json");
    
    exit(json_encode([
        "data" => [
            "white" => $_POST["text"],
            "black" => $_POST["text"]
        ]
    ]));
?>