<?php

if ( $_SERVER['argc'] < 3 )
	die( "fluidbuilder fetchingstrategy [name]\n" );

$name = $_SERVER['argv'][2];


create_directory( "./src/FetchingStrategy" );
write_file( "./src/FetchingStrategy/" . ucfirst( $name ) . ".php", get_content( getAbsoluteFluidTemplateDirectory() . "/FetchingStrategy.php", $name ) );
