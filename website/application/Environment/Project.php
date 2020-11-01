<?php
    define("PROJECT", [
        "NAME" => getenv("PROJECT_NAME"),
        "CURRENCY" => getenv("PROJECT_CURRENCY"),
        "DISCORD" => getenv("PROJECT_DISCORD"),
        "DOMAIN" => getenv("WEBSITE_DOMAIN"), // TODO: Split this?
        "DEBUGGING" => (bool)getenv("PROJECT_DEBUGGING"),
        "COOKIE_POLICY" => getenv("PROJECT_COOKIE_POLICY"),
        "COMMUNISM" => (bool)getenv("PROJECT_COMMUNISM"),
        "TIMEZONE" => getenv("PROJECT_TIMEZONE"),
        "REWARD" => [
            "TIMEOUT" => intval(getenv("PROJECT_REWARD_TIMEOUT")),
            "AMOUNT" => intval(getenv("PROJECT_REWARD_AMOUNT"))
        ],
        "PRIVATE" => [
            "LOCKDOWN" => (bool)getenv("PROJECT_PRIVATE_LOCKDOWN"),
            "IMPLICATION" => (bool)getenv("PROJECT_PRIVATE_IMPLICATION"),
            "REFERRAL" => (bool)getenv("PROJECT_PRIVATE_REFERRAL"),
            "INVITE_ONLY" => (bool)getenv("PROJECT_PRIVATE_INVITE_ONLY")
        ],
        "VALID_EMAIL_DOMAINS" => ["rboxlo.xyz", "google.com", "protonmail.ch", "googlemail.com", "gmail.com", "yahoo.com", "yahoomail.com", "protonmail.com", "outlook.com", "hotmail.com", "microsoft.com", "inbox.com", "mail.com", "zoho.com"] // TODO: Move this somewhere else
    ]);
?>