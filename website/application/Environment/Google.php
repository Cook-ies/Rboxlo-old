<?php
    define("GOOGLE", [ 
        "ANALYTICS" => [
            "ENABLED" => boolval(getenv("GOOGLE_ANALYTICS_ENABLED")),
            "TAG" => getenv("GOOGLE_ANALYTICS_TAG")
        ],
        "RECAPTCHA" => [
            "PUBLIC_KEY" => getenv("GOOGLE_RECAPTCHA_PUBLIC_KEY"),
            "PRIVATE_KEY" => getenv("GOOGLE_RECAPTCHA_PRIVATE_KEY")
        ]
    ]);
?>