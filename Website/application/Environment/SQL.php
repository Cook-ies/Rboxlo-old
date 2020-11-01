<?php
    define("SQL", [
        "USERNAME" => getenv("DB_USERNAME"),
        "PASSWORD" => getenv("DB_PASSWORD"),
        "DATABASE" => getenv("DB_DATABASE"),
        "PORT"     => getenv("DB_PORT")
    ]);
?>