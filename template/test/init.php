<?php
require_once 'PHPUnit/Framework.php';
$GLOBALS['testing'] = 1;
$GLOBALS['loggedInUser']['id'] = -1;


chdir( getcwd() . "/../src/" );
require_once "_init.php";
