<?php

if ( $_SERVER['argc'] < 4 )
	die( "fluidbuilder pc search [name]\n" );


$name = $_SERVER['argv'][3];


write_file( "./src/" . $name . "_search.php", get_content( getAbsoluteFluidTemplateDirectory() . "/search.php", $name ) );
write_file( "./src/" . $name . "_search_result.php", get_content( getAbsoluteFluidTemplateDirectory() . "/search_result.php", $name ) );
