<?php

if ( $_SERVER['argc'] < 3 )
	die( "fluidbuilder domaineventhandler [name]\n" );

$name = $_SERVER['argv'][2];


create_directory( "./src/DomainEventHandler" );
write_file( "./src/DomainEventHandler/" . ucfirst( $name ) . ".php", get_content( getAbsoluteFluidTemplateDirectory() . "/DomainEventHandler.php", $name ) );
