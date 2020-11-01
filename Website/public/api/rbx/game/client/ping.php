<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/RBX.php");
    
    function send_message($success)
    {
        exit(json_encode([
            "success" => $success
        ]));
    }

    if (!isset($_SESSION["user"]))
    {
        send_message($false);
    }

    open_database_connection($sql);

    $statement = $sql->prepare("SELECT `last_ping` FROM `users` WHERE `id` = ?");
    $statement->execute([$_SESSION["user"]["id"]]);
    $last_ping = json_encode(json_decode($statement->fetch(PDO::FETCH_ASSOC)["last_ping"], true)["client"] = time());
    
    $statement = $sql->prepare("UPDATE `users` SET `last_ping` = ? WHERE `id` = ?");
    $statement->execute([$last_ping, $_SESSION["user"]["id"]]);

    close_database_connection($sql, $statement);
    
    send_message(true);
?>