<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../application/rbx.php");
    header("Content-Type: text/plain");

    $ticket = $_SERVER["HTTP_ROBLOX-SESSION-ID"] . ";" . $_SERVER["HTTP_ROBLOX-GAME-ID"] . ";" . date("n/j/Y g:i:s A");
    $result = "<Value Type=\"string\">". get_signature($ticket) . "</Value>";
    
    exit($result);
?>