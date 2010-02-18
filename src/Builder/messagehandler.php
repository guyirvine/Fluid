<?php

if ( $_SERVER['argc'] < 3 )
	die( "fluidbuilder messagehandler [name]\n" );

$name = $_SERVER['argv'][2];


create_directory( "./src/MessageHandler" );
write_file( "./src/FluidMq.php", get_content( getAbsoluteFluidDirectory() . "/src/Mq/rec.php", $name ) );
write_file( "./src/MessageHandler/" . ucfirst( $name ) . ".php", get_content( getAbsoluteFluidTemplateDirectory() . "/MessageHandler.php", $name ) );
