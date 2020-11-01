<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../application/rbx.php");

    if (!isset($_GET["id"]) || strlen($_GET["id"]) <= 0 || !is_int((int)$_GET["id"])) // I use strlen instead of empty because empty returns "false" if it's "falsey", e.g asset id "0"
    {
        http_response_code(404);
        exit();
    }

    open_database_connection($sql);
    
    $id = filter_var($_GET["id"], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
    $version = isset($_GET["version"]) ? filter_var($_GET["version"], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE) : -1; // -1 is the latest version internally.
    
    if (!isset($id) || empty($id) || $id == NULL)
    {
        // We at least need an asset id
        http_response_code(404);
        exit();
    }

    // See if the asset in our database exists
    $statement = $sql->prepare("SELECT `permissions`, `hash`, `history`, `type` FROM `assets` WHERE `id` = ?");
    $statement->execute([$id]);
    $asset = $statement->fetch(PDO::FETCH_ASSOC);

    if ($asset) // If asset exists in our database
    {
        // There is an asset from our database. Lets decode its result!
        $history = json_decode($asset["history"]);
        $permissions = explode(";", $asset["permissions"]);
        $verified = empty($permissions); // if the permissions are empty, assume uncopylocked
        
        if (!$verified) // if copylocked, verify permissions
        {
            // Verify permissions based on ID and IP address
            // Are we logged in? If so, lets verify based on that
            if (isset($_SESSION["logged_in"]))
            {
                if (array_key_exists($_SESSION["id"], $permissions)) // array_key_exists instead to signify that we are looking for key, rather than based on index
                {
                    $verified = true;
                }
            }
            else // We aren't logged in, so lets get a user id who last logged in with our IP address
            {
                $statement = $sql->prepare("SELECT `id` FROM `users` WHERE `last_ip` = ?");
                $statement->execute([get_user_ip()]);
                $result = $statement->fetch(PDO::FETCH_ASSOC);
                if ($result)
                {
                    if (array_key_exists($result["id"], $permissions))
                    {
                        $verified = true;
                    }
                }
            }
        }

        if ($verified)
        {
            // Now deliver the asset
            
            if ($version != -1 || $version != $asset["history"]["latest"]) // rather than return latest version hash in history["latest"] we already have it in the column
            {
                // See if this is a different version than current. If so, return the old file version hash
                $statement = $sql->prepare("SELECT `hash` FROM `asset_hashes` WHERE `version` = ? AND `id` = ?");
                $statement->execute([$hash, $id]);
                $result = $statement->fetch(PDO::FETCH_ASSOC);
                
                if ($result)
                {
                    $hash = $result["hash"];
                }
            }

            $file = $_SERVER["DOCUMENT_ROOT"] . "/../data/assets/$hash";

            if ($asset["type"] == "corescript")
            {
                // If we are a corescript, or other signed media, we need to assume different instructions.
                // We will need to return the asset requested as usual, but signed.
                
                // We need to get the version intended for this corescript so as to figure out signature format
                // "0" = corescript version 2014 and above, "1" = corescript version 2013 and below. Any others will result in the absence of an outputted signature
                // difference in format is the absence of "--rbxsig" and "--rbxid"

                $statement = $sql->prepare("SELECT `corescript_version` FROM `assets` WHERE `id` = ?"); //btw, normally "corescript_version" is 0 for non corescripts
                $statement->execute([$id]);
                $result = $statement->fetch(PDO::FETCH_ASSOC);

                close_database_connection($sql, $statement); // We're done using the database

                $corescript_version = $result["corescript_version"];
                $script = file_get_contents($file);
                $signature = get_signature($script);
                
                if ($corescript_version == 0)
                {
                    echo("--rbxsig%$signature%\n");
                    echo("--rbxid%$id%\n");
                }
                elseif ($corescript_version == 1)
                {
                    echo("%$signature%\n");
                }

                echo($script); // TODO: Should we really use "echo" for this?
                exit(); // We've delivered it, now stop
            }

            close_database_connection($sql, $statement);

            // Output file
            header("Content-Length: " . filesize($file));
            header("Content-Type: binary/octet-stream");
            readfile($file); 

            exit(); // We've delivered it, now stop
        }

        close_database_connection($sql, $statement);

        // We didn't pass asset verification and fell through, return 409
        http_response_code(409);
        exit();
    }
    else
    {
        // This might be a Roblox asset. Lets fetch it from Roblox
        $url = "https://assetdelivery.roblox.com/v1/assetId/$id";
        if ($version != -1 || $version != NULL) // Get specific version
        {
            $url .= "/version/$version";
        }
        
        $options = ["http" => ["user_agent" => "Roblox"]]; // Set user agent because of KTX
        $context = stream_context_create($options);
        $asset = @file_get_contents($url, false, $context);

        if ($asset)
        {
            $asset = json_decode($asset, true);
            if (isset($asset["errors"]))
            {
                if ((int)$asset["errors"][0]["code"] == 0)
                {
                    // We messed up something
                    http_response_code(400);
                    exit();
                }

                http_response_code((int)$asset["errors"][0]["code"]); // handles 404, 409, etc.
                exit();
            }

            // Required for conflict between "Location" and "location"
            foreach ($asset as $key => $value)
            {
                $asset[trim(strtolower($key))] = trim($value);
            }

            // Deliver Roblox asset
            $file = file_get_contents($asset["location"]);
            $headers = parse_response_headers($http_response_header); // $http_response_header materializes out of thin air. parse_response_headers is our own function
            
            header("Content-Length: " . $headers["Content-Length"]);
            header("Content-Type: " . $headers["Content-Type"]);
            header("Content-Disposition: inline; filename='". trim(end(explode("/", $asset["location"]))) . "'");
            exit($file); // Return it
        }
        else
        {
            // Internal error
            http_response_code(400);
            exit();
        }  
    }
    
    // We fell through literally everything. This shouldn't ever happen, but if this is the case, then lets not leave holes open
    close_database_connection($sql, $statement);
    http_response_code(404);
    exit();