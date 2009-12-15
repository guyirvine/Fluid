<?php

if ( $_SERVER['argc'] < 4 )
	die( "fluidbuilder pc grid [name]\n" );


$name = $_SERVER['argv'][3];


write_file( "./src/$name.php", get_content( getAbsoluteFluidTemplateDirectory() . "/grid.php", $name  ) );

add_fragment( $name );
add_domain_concept( $name );
