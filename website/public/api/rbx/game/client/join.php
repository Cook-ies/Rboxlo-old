<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/RBX.php");
    header("Content-Type: text/plain");

    open_database_connection($sql);

    if (!isset($_GET["token"]) || empty($_GET["token"]) || !ctype_alnum($_GET["token"]))
    {
        exit("Invalid token");
    }

    $statement = $sql->prepare("SELECT * FROM `join_tokens` WHERE `token` = ?");
    $statement->execute([$_GET["token"]]);
    $token = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$token)
    {
        exit("No token");
    }

    $token["attributes"] = json_decode($token["attributes"], true);
    if ($token["attributes"]["usage"] != "game")
    {
        exit("Invalid token");
    }

    $statement = $sql->prepare("SELECT `username`, `created` FROM `users` WHERE `id` = ?");
    $statement->execute([$token["user_id"]]);
    $user = $statement->fetch(PDO::FETCH_ASSOC) ?? ["banned" => true];
    if (!$user || $user["banned"])
    {
        exit("No user");
    }

    $statement = $sql->prepare("SELECT `game_id`, `chat_style` FROM `places` WHERE `id` = ?");
    $statement->execute([$token["place_id"]]);
    $place = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$place)
    {
        exit("No place");
    }

    $statement = $sql->prepare("SELECT `uuid`, `privileges` FROM `games` WHERE `id` = ?");
    $statement->execute([$place["game_id"]]);
    $game = $statement->fetch(PDO::FETCH_ASSOC) ?? ["client_version" => -1];
    if (!$game || $game["client_version"] != 3 || $game["client_version"] != 4)
    {
        exit("No game");
    }
    $game["privileges"] = json_decode($game["privileges"], true);

    $statement = $sql->prepare("SELECT `ip`, `port` FROM `jobs` WHERE `id` = ?");
    $statement->execute([$token["attributes"]["job_id"]]);
    $job = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$job)
    {
        exit("No job");
    }

    $elapsed = time() - intval($token["generated"]);
    if ($elapsed >= 300)
    {
        $statement = $sql->prepare("DELETE FROM `join_tokens` WHERE `token` = ?");
        $statement->execute([$_GET["token"]]);
        exit("Token expired");
    }

    // Kill token
    $statement = $sql->prepare("DELETE FROM `game_tokens` WHERE `token` = ?");
    $statement->execute([$token["token"]]);

    // Get exact time
    $exact_time = date("Y-d-m") . "T" . date("H:i:s.") . substr(milliseconds(), 0, 7) . "Z";
    
    // Construct session id
    $session_id = get_random_guid() . "|" . $game["uuid"] . "|" . $place["id"] . "|". get_user_ip() . "|0|". $exact_time . "|0|null|null" . ($game["client_version"] == 3 ? "|0|0|0" : "");

    // Construct joinscript
    $joinscript = [
        "ClientPort" => 0,
        "MachineAddress" => $job["ip"],
        "ServerPort" => $job["port"],
        "PingUrl" => get_server_host() . "/endpoints/rbx/game/client/ping?id=". $user["id"] ."&place=". $place["id"],
        "PingInterval" => 20,
        "UserName" => $user["username"],
        "SeleniumTestMode" => false,
        "UserId" => $user["id"],
        "SuperSafeChat" => false,
        "CharacterAppearance" => get_server_host() . "/v1.1/avatar-fetch/?placeId=". $place["id"] ."&userId=". $user["id"],
        "ClientTicket" => "",
        "GameId" => $game["uuid"],
        "PlaceId" => $token["place_id"],
        "MeasurementUrl" => "", // No telemetry here :)
        "WaitingForCharacterGuid" => get_random_guid(),
        "BaseUrl" => get_server_host() . "/",
        "ChatStyle" => $place["chat_style"],
        "VendorId" => "0",
        "ScreenShotInfo" => "",
        "VideoInfo" => "",
        "CreatorId" => $game["privileges"]["creator"],
        "CreatorTypeEnum" => "User",
        "MembershipType" => "None",
        "AccountAge" => round((time() - $user["creator"]) / 86400),
        "CookieStoreFirstTimePlayKey" => "rbx_evt_ftp",
        "CookieStoreFiveMinutePlayKey" => "rbx_evt_fmp",
        "CookieStoreEnabled" => true,
        "IsRobloxPlace" => $place["trusted"],
        "GenerateTeleportJoin" => false,
        "IsUnknownOrUnder13" => false, // You have to be 13+ to sign up...
        "SessionId" => $session_id,
        "DataCenterId" => 0,
        "UniverseId" => $place["id"],
        "BrowserTrackerId" => 0,
        "UsePortraitMode" => false,
        "FollowUserId" => 0,
        "characterAppearanceId" => $user["id"]
    ];

    // Encode it!
    $data = json_encode($joinscript, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

    // Sign joinscript
    $signature = get_signature("\r\n" . $data);

    // exit
    exit("--rbxsig%". $signature . "%\r\n" . $data);
?>