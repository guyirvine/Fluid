<?php

if ( $_SERVER['argc'] < 2 )
	die( "fluidbuilder fluidmq\n" );


write_file( "./src/FluidMq.php", get_content( getAbsoluteFluidTemplateDirectory() . "/FluidMq.php", $name ) );
