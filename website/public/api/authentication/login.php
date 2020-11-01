<?php 
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Includes.php");
    header("Content-Type: application/json");
    open_database_connection($sql);
    
    // Defaults to an error
    $success = false;
    $message = "An unexpected error occurred.";

    $error = false;
                    
    if (!isset($_POST["information"]))
    {
        $message = "Nothing was sent.";
        $error = true;
    }

    if (isset($_SESSION["user"]))
    {
        $message = "You are already logged in!";
        $error = true;
    }

    if (!$error)
    {
        $information = json_decode($_POST["information"], true);
        
        if ($information["csrf"] !== $_SESSION["csrf"] && !$error)
        {
            $message = "Invalid CSRF token.";
            $error = true;
        }

        if (!isset($information["username"]) || empty($information["username"]) || strlen($information["username"]) <= 0 && !$error)
        {
            $message = "In order to sign in, you need to specify a username.";
            $error = true;
        }

        if (!isset($information["password"]) || empty($information["password"]) || strlen($information["password"]) <= 0 && !$error)
        {
            $message = "In order to sign in, you need to specify a password.";
            $error = true;
        }

        if (!$error)
        {
            $statement = $sql->prepare("SELECT * FROM `users` WHERE `username` = ? OR `email` = ?");
            $statement->execute([$information["username"], $information["username"]]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);

            if ($result)
            {
                if (password_verify($information["password"], _crypt($result["password"], "decrypt")))
                {
                    // Do we need more money
                    // PS: We only reward once if it's over like say 2 days. No money stacking :D
                    if (($result["next_reward"] - time()) >= PROJECT["REWARD"]["TIMEOUT"])
                    {
                        $statement = $sql->prepare("UPDATE `users` SET `money` = ?, `next_reward` = ? WHERE `id` = ?");
                        $statement->execute([(intval($result["money"]) + PROJECT["REWARD"]["AMOUNT"]), (time() + PROJECT["REWARD"]["TIMEOUT"])]);
                    }

                    // Set our last ping time
                    $last_ping = json_decode($result["last_ping"], true);
                    $last_ping["website"] = time();
                    $last_ping = json_encode($last_ping);

                    $statement = $sql->prepare("UPDATE `users` SET `last_ping` = ? WHERE `id` = ?");
                    $statement->execute([$last_ping]);

                    // Set our session
                    $_SESSION["user"] = $result;
                    
                    // Erase sensitive information from session
                    $_SESSION["user"]["password"] = "";
                    $_SESSION["user"]["email"] = "";
                    $_SESSION["user"]["2fa_secret"] = "";
                    $_SESSION["user"]["ip_history"] = "";

                    // Parse
                    $_SESSION["user"]["permissions"] = json_decode($result["permissions"], true);
                    $_SESSION["user"]["avatar"] = json_decode($result["avatar"], true);

                    $success = true;
                    $message = "Welcome back, ". $result["username"] ."! Redirecting you to your dashboard...";
                }
                else
                {
                    $message = "Invalid credentials!";
                }
            }
            else
            {
                $message = "That user could not be found!";
            }
        }
    }

    close_database_connection($sql, $database);
    
    exit(json_encode([
        "success" => $success,
        "message" => $message
    ]));
?>