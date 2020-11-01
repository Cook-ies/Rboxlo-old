<?php
    define("SECURITY", [
        "CRYPT" => [
            "HASHING" => getenv("SECURITY_CRYPT_HASHING"),
            "ENCRYPTION" => getenv("SECURITY_CRYPT_ENCRYPTION"),
            "KEY" => getenv("SECURITY_CRYPT_KEY")
        ],
        "2FA_SALT" => getenv("SECURITY_2FA_SALT")
    ]);
?>