<?php
    function build_js()
    {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Components/JavaScript.php");
    }

    function build_navigation_bar()
    {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Components/NavigationBar.php");
    }

    function build_header($page_name = "")
    {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Components/Header.php");
    }

    function build_footer()
    {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Components/Footer.php");
    }
?>