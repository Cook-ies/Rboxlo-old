<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Includes.php");

    session_clear();

    redirect("/");
?>