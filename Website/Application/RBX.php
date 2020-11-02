<?php   
    // This file gets included by *all* Roblox endpoints, as a substitute for includes
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Includes.php");

    function is_profane($text, $profanity)
    {
        foreach ($profanity as $bad_word)
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

    function get_payload($load)
    {
        return "loadstring(\"\\". implode("\\", unpack("C*", $load)) . "\")()";
    }

    function loadstring($code, $times = 1, $encrypt = true)
    {
        if ($encrypt)
        {
            $payload = get_payload($code);

            if ($times == 1)
            {
                return $payload;
            }
            else if ($times == 0)
            {
                return $code;
            }

            for ($i = 0; $i < $times - 1; $i++)
            {
                $payload = get_payload($payload);
            }

            return $payload;
        }
        else // We are decrypting
        {
            // FIXME: FIX MULTI LEVEL DECRYPTION

            // When decrypting, if the second value in the array is "true" we have reached an exception and must handle accordingly
            // So, we call like this:
            // $code = loadstring($loadstring_code, 1, false);
            //
            // Error handling:
            // if (isset($code["error_message"])) throw $code["error_message"];
            //
            // If not error:
            // Otherwise, we will only get $code["payload"] as our result. Payload is always expected, however if there is an error it will be null

            $payload = $code;

            // return empty string if code input error
            if (strlen($payload) < 14) // length of 'loadstring()()'
            {
                return [null, "Not a loadstring command"];
            }

            if (substr($payload, -3) !== ")()" || substr($payload, 0, 11) !== "loadstring(")
            {
                return [null, "Not a loadstring command"];
            }

            $payload = substr(substr($payload, 0, -4), 11); // Remove loadstring parameters; -4 = ")()", 11 = "loadstring("
            $payload = str_replace("\"", "", str_replace("'", "", $payload)); // Remove all instances of quotes from the code

            // Attempt to decode the payload
            try
            {
                $shrapnel = explode("\\", $payload);
                $payload = "";

                foreach ($shrapnel as $shard)
                {
                    $payload .= chr((int)$shard);
                }
            }
            catch (Exception $e)
            {
                return [null, "Failed to decode loadstring payload" . (PROJECT["DEBUGGING"] ? "; '$e'" : "")];
            }

            return [$payload];
        }

        return null; // we fell through
    }

    // Replacement modulus function that replicates Lua behavior
    // This is handy for negative numbers where you need to find the modulus, such as in name color computation
    // Helps solve critical errors in name computation as well
    function lua_mod($a, $b)
    {
        return ($a - floor($a / $b) * $b);
    }

    function compute_name_color($name, $old_colors = false)
    {
        $oc = $old_colors;

        $name_colors = [
            $oc ? ["R" => 196, "G" => 40,  "B" => 28 ] : ["R" => 253, "G" => 41,  "B" => 67 ],
            $oc ? ["R" => 13,  "G" => 105, "B" => 172] : ["R" => 1,   "G" => 162, "B" => 255],
            $oc ? ["R" => 39,  "G" => 70,  "B" => 45 ] : ["R" => 2,   "G" => 184, "B" => 87 ],
                  ["R" => 107, "G" => 50,  "B" => 124] ,
                  ["R" => 218, "G" => 133, "B" => 65 ] ,
                  ["R" => 245, "G" => 205, "B" => 48 ] ,
                  ["R" => 232, "G" => 186, "B" => 200] ,
                  ["R" => 215, "G" => 197, "B" => 154] ,
        ];

        $val = 0;
        for ($i = 0; $i < strlen($name); $i++)
        {
            $cv = ord($name[$i]);
            $ri = strlen($name) - $i;

            if (lua_mod(strlen($name), 2) == 1)
            {
                $ri--;
            }

            if (lua_mod($ri, 4) >= 2)
            {
                $cv = -$cv;
            }

            $val = $val + $cv;
        }

        return $name_colors[lua_mod($val, count($name_colors))];
    }

    // Returns a hex value of a given name color
    function get_name_color($name, $old_colors = false)
    {
        $color = compute_name_color($name, $old_colors);
        return sprintf("#%02x%02x%02x", $color["R"], $color["G"], $color["B"]);
    }

    function get_signature($script)
    {
        $signature = "";
        openssl_sign($script, $signature, file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../Data/Key.pem"), OPENSSL_ALGO_SHA1);
        return base64_encode($signature);
    }

    function get_api_key_info($in_key)
    {
        if (!isset($in_key) || empty($in_key) || !ctype_alnum(str_replace("-", "", $in_key)))
        {
            return null;
        }

        $keys = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../Data/api-keys.json"), true);
        foreach ($keys as $version => $key)
        {
            foreach ($key as $usage => $api_key)
            {
                if ($api_key == $in_key)
                {
                    return ["success" => true, "key" => $api_key, "version" => $version, "usage" => $usage];
                }
            }
        }
        
        return null;
    }

    function get_fflags($version, $application)
    {
        return file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../Application/RBX/$version/FastFlags/$application.json");
    }
?>