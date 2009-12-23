<?php

if ( $_SERVER['argc'] < 3 )
	die( "fluidbuilder cache [name]\n" );

$name = $_SERVER['argv'][2];


create_directory( "./src/Cache" );
write_file( "./src/Cache/" . ucfirst( $name ) . ".php", get_content( getAbsoluteFluidTemplateDirectory() . "/Cache.php", $name ) );
