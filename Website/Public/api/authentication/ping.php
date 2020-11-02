<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Includes.php");

    if (!isset($_SESSION["user"]))
    {
        exit(json_encode(["success" => false]));
    }

    open_database_connection($sql);

    $statement = $sql->prepare("SELECT `last_ping` FROM `users` WHERE `id` = ?");
    $statement->execute([$_SESSION["user"]["id"]]);

    $last_ping = json_decode($statement->fetch(PDO::FETCH_ASSOC)["last_ping"], true);
    $last_ping = json_encode($last_ping["website"] = time());

    $statement = $sql->prepare("UPDATE `users` SET `last_ping` = ? WHERE `id` = ?");
    $statement->execute([$_SESSION["user"]["id"]]);

    exit(json_encode(["success" => true]));
?>