<?php
$GLOBALS['dbname'] = '===name===';

function get_connection_string() {
    return "host=localhost dbname=" . $GLOBALS['dbname'] . " user====name=== password====name===";
}

