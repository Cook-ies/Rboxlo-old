<?php   
    // This file gets included by *all* Roblox endpoints.
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../backend/includes.php");
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../backend/rbx/soap.php");

    function is_profane($text)
    {
        foreach (PROFANITY as $bad_word)
        {
            if (strpos($text, $bad_word) !== false)
            {
                return true;
            }
        }

        return false;
    }
    
    function filter_profanity($text)
    {
        return($text);
    }

    function get_signature($script)
    {
        $key = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../static/key.pem");
        $signature;

        openssl_sign($script, $signature, $key, OPENSSL_ALGO_SHA1);

        return base64_encode($signature);
    }

    function get_api_key($key)
    {
        if (!isset($key) || empty($key) || !ctype_alnum(str_replace("-", "", $key)))
        {
            return false;
        }

        $statement = $GLOBALS["sql"]->prepare("SELECT `version`, `usage` FROM `api_keys` WHERE `key` = ?");
        $statement->execute([$key]);
        
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    function get_fflags($version, $application)
    {
        return file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../backend/rbx/$version/fastflags/$application.json");
    }

    function create_job($ip, $id, $information)
    {
        soap_send_envelope($ip, "OpenJob", soap_get_envelope("OpenJob", [
            [
                "submethod" => "job",
                "details" => [
                    "id" => $information["id"],
                    "expirationInSeconds" => $information["expiration"],
                    "cores" => $information["cores"],
                    "category" => $information["category"]
                ]
            ],
            [
                "submethod" => "script",
                "details" => [
                    "name" => "Start Server",
                    "script" => $information["script"]
                ]
            ]
        ]));
    }

    function close_job($ip, $id)
    {
        soap_send_envelope($ip, "CloseJob", soap_get_envelope("CloseJob", [
            [
                "submethod" => "jobID",
                "content" => $id
            ]
        ]));
    }
?>