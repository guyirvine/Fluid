<?php
require_once 'init.php';
require_once 'Fluid/rest_fns.php';


$msgName = $path_list[0];


$path = 'ajax/' . $msgName . ".php";
if ( is_file( $path ) )
	require_once $path;

